<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Products\ProductShow;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

/**
 * @group livewire.traits
 */
class HasPricesTest extends TestCase
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
        ]);
    }

    /** @test */
    public function can_pass_pricing_to_save()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $defaultLanguage = Language::factory()->create([
            'default' => true,
        ]);

        Language::factory()->create([
            'default' => false,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id' => $variant->id,
                'currency_id' => $currency->id,
                'tier' => 1,
            ]);
        }

        $newPricing = [];

        $newTierPricing = [];

        foreach (Currency::get() as $currency) {
            $newPricing[$currency->code] = [
                'currency_id' => $currency->id,
                'price' => 123,
                'tier' => 1,
            ];

            $newTierPricing[$currency->code] = [
                'prices' => [[
                    'price' => 124,
                    'currency_id' => $currency->id,
                ]],
                'tier' => 2,
                'customer_group_id' => '*',
            ];
        }

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ]);

        $component->call(
            'savePricing',
            collect($newPricing),
            collect($newTierPricing)
        );

        $this->assertDatabaseHas((new Price)->getTable(), [
            'price' => 12300,
            'tier' => 1,
        ]);

        $this->assertDatabaseHas((new Price)->getTable(), [
            'price' => 12400,
            'tier' => 2,
        ]);
    }
}
