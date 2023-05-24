<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Products;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Hub\Http\Livewire\Components\Products\ProductCreate;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\Url;

/**
 * @group hub.products
 */
class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        Language::factory()->create([
            'default' => false,
            'code' => 'fr',
        ]);

        Currency::factory()->create([
            'default' => true,
            'decimal_places' => 2,
        ]);

        TaxClass::factory()->create([
            'default' => true,
        ]);

        ProductType::factory()->create();
    }

    /** @test */
    public function component_mounts_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->assertViewIs('adminhub::livewire.components.products.create');
    }

    /** @test */
    public function validation_triggers()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::getDefault();

        $language = Language::getDefault();

        $collection = Collection::factory()->create();

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->call('save')
            ->assertHasErrors([
                'product.brand_id',
            ]);
    }

    /** @test */
    public function can_create_product()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::getDefault();

        $language = Language::getDefault();

        $productB = Product::factory()->create([
            'status' => 'published',
        ]);

        $productC = Product::factory()->create([
            'status' => 'published',
        ]);

        $brand = Brand::factory()->create();

        $collection = Collection::factory()->create();

        $this->assertDatabaseMissing((new Product)->collections()->getTable(), [
            'collection_id' => $collection->id,
        ]);

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set('variant.sku', '1234')
            ->set('variant.tax_ref', 'CUSTOMTAX')
            ->set("basePrices.{$currency->code}.price", 1234)
            ->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->set('product.brand_id', $brand->id)
            ->set('associations', collect([
                [
                    'inverse' => false,
                    'target_id' => $productB->id,
                    'thumbnail' => optional($productB->thumbnail)->getUrl('small'),
                    'name' => $productB->translateAttribute('name'),
                    'type' => 'cross-sell',
                ],
                [
                    'inverse' => true,
                    'target_id' => $productC->id,
                    'thumbnail' => optional($productC->thumbnail)->getUrl('small'),
                    'name' => $productC->translateAttribute('name'),
                    'type' => 'cross-sell',
                ],
            ]))->set('collections', collect([
                [
                    'id' => $collection->id,
                    'name' => $collection->translateAttribute('name'),
                    'group_id' => $collection->collection_group_id,
                    'group_name' => $collection->group->name,
                    'thumbnail' => null,
                    'breadcrumb' => ['Foo', 'Bar'],
                    'position' => 1,
                ],
            ]))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas((new ProductAssociation)->getTable(), [
            'product_target_id' => $productB->id,
            'product_parent_id' => $component->get('product.id'),
            'type' => 'cross-sell',
        ]);

        $this->assertDatabaseHas((new ProductAssociation)->getTable(), [
            'product_parent_id' => $productC->id,
            'product_target_id' => $component->get('product.id'),
            'type' => 'cross-sell',
        ]);

        $this->assertDatabaseHas((new Product)->collections()->getTable(), [
            'collection_id' => $collection->id,
            'product_id' => $component->get('product.id'),
        ]);

        $this->assertDatabaseHas((new ProductVariant)->getTable(), [
            'sku' => '1234',
            'tax_ref' => 'CUSTOMTAX',
        ]);

        $this->assertDatabaseHas((new Price)->getTable(), [
            'price' => '123400',
        ]);

        $this->assertDatabaseCount((new Price)->getTable(), 1);

        $this->assertDatabaseHas((new Url)->getTable(), [
            'slug' => 'foo-bar',
            'element_type' => Product::class,
            'element_id' => $component->get('product.id'),
        ]);
    }

    /** @test */
    public function validates_required_translated_text_attribute()
    {
        // value for attribute in default language MUST be REQUIRED
        // value for attribute in additional languages are optional
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $language = Language::getDefault();

        $attribute = Attribute::factory()->create([
            'type' => TranslatedText::class,
            'required' => true,
        ]);

        $productType = ProductType::first();

        $productType->mappedAttributes()->attach($attribute);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->call('save')
            ->assertHasErrors([
                "attributeMapping.a_{$attribute->id}.data.{$language->code}" => 'required',
            ])
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data.fr" => 'required',
            ]);
    }

    /** @test */
    public function validates_optional_translated_text_attribute()
    {
        // optional attribute must only apply user set validation rules
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $language = Language::getDefault();

        $attribute = Attribute::factory()->create([
            'type' => TranslatedText::class,
            'required' => false,
            'validation_rules' => 'string,max:9',
        ]);

        $productType = ProductType::first();

        $productType->mappedAttributes()->attach($attribute);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data.{$language->code}", 'more than ten letters')
            ->call('save')
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data.{$language->code}" => 'required',
            ])
            ->assertHasErrors([
                "attributeMapping.a_{$attribute->id}.data.{$language->code}" => 'max',
            ]);
    }

    /** @test */
    public function validates_required_text_attribute()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $attribute = Attribute::factory()->create([
            'type' => Text::class,
            'required' => true,
        ]);

        $productType = ProductType::first();

        $productType->mappedAttributes()->attach($attribute);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->call('save')
            ->assertHasErrors([
                "attributeMapping.a_{$attribute->id}.data" => 'required',
            ]);

        Livewire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data", 'some value')
            ->call('save')
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data" => 'required',
            ]);
    }

    /** @test */
    public function validates_optional_text_attribute()
    {
        // with additional rules
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $attribute = Attribute::factory()->create([
            'type' => Text::class,
            'required' => false,
            'validation_rules' => 'numeric',
        ]);

        $productType = ProductType::first();

        $productType->mappedAttributes()->attach($attribute);

        // it is not required for realsies
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->call('save')
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data" => 'required',
            ]);

        // fail the additional validation rules
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data", 'text not a number')
            ->call('save')
            ->assertHasErrors([
                "attributeMapping.a_{$attribute->id}.data" => 'numeric',
            ]);

        // pass the validation
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data", '42')
            ->call('save')
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data",
            ]);
    }

    /** @test */
    public function validates_required_number_attribute()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $attribute = Attribute::factory()->create([
            'type' => Number::class,
            'required' => true,
            'configuration' => ['min' => 2, 'max' => 5],
        ]);

        $productType = ProductType::first();

        $productType->mappedAttributes()->attach($attribute);

        // fail the required rule
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->call('save')
            ->assertHasErrors([
                "attributeMapping.a_{$attribute->id}.data" => 'required',
            ]);

        // pass the required rule
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data", 3)
            ->call('save')
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data" => 'required',
            ]);
    }

    /** @test */
    public function validates_optional_number_attribute()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $attribute = Attribute::factory()->create([
            'type' => Number::class,
            'required' => true,
            'configuration' => ['min' => 2, 'max' => 5],
        ]);

        $productType = ProductType::first();

        $productType->mappedAttributes()->attach($attribute);

        // fail the configuration rules
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data", 10)
            ->call('save')
            ->assertHasErrors([
                "attributeMapping.a_{$attribute->id}.data",
            ]);

        // pass the configuration rules
        LiveWire::actingAs($staff, 'staff')
            ->test(ProductCreate::class)
            ->set("attributeMapping.a_{$attribute->id}.data", 3)
            ->call('save')
            ->assertHasNoErrors([
                "attributeMapping.a_{$attribute->id}.data",
            ]);
    }
}
