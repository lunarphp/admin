<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Currencies;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Settings\Currencies\CurrencyShow;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;

/**
 * @group hub.currencies
 */
class CurrencyShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_currency()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::factory()->create([
            'default' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $properties = [
            'name' => 'Some currency name',
            'code' => 'TST',
            'default' => true,
            'exchange_rate' => 0.5,
            'enabled' => 0,
        ];

        $component = Livewire::test(CurrencyShow::class, [
            'currency' => $currency,
        ]);

        foreach ($properties as $property => $value) {
            $component->set("currency.$property", $value);
        }

        $component->call('update');

        $this->assertFalse($currency->default);

        $currency = $currency->refresh();

        foreach ($properties as $property => $value) {
            $this->assertEquals($value, $currency->{$property});
        }
    }

    /** @test */
    public function currency_must_have_unique_code()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        Currency::factory()->create([
            'default' => false,
            'code' => 'FOO',
        ]);

        $currency = Currency::factory()->create([
            'default' => false,
        ]);

        $this->actingAs($staff, 'staff');

        Livewire::test(CurrencyShow::class, [
            'currency' => $currency,
        ])->set('currency.code', 'FOO')
            ->call('update')
            ->assertHasErrors(['currency.code' => 'unique']);
    }

    /** @test */
    public function cant_delete_a_currency_without_confirming_code()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::factory()->create([
            'default' => false,
            'code' => 'FOO',
        ]);

        $this->actingAs($staff, 'staff');

        Livewire::test(CurrencyShow::class, [
            'currency' => $currency,
        ])->set('deleteConfirm', 'BAR')
            ->call('delete');

        $this->assertNull($currency->refresh()->deleted_at);
    }

    /** @test */
    public function can_delete_a_currency_by_confirming_code()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $currency = Currency::factory()->create([
            'default' => false,
            'code' => 'FOO',
        ]);

        $this->actingAs($staff, 'staff');

        Livewire::test(CurrencyShow::class, [
            'currency' => $currency,
        ])->set('deleteConfirm', 'FOO')
            ->call('delete');

        $this->assertDatabaseMissing($currency->getTable(), [
            'name' => $currency->name,
            'code' => $currency->code,
        ]);
    }
}
