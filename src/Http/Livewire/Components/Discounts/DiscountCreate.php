<?php

namespace Lunar\Hub\Http\Livewire\Components\Discounts;

use Illuminate\Support\Str;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Currency;
use Lunar\Models\Discount;

class DiscountCreate extends AbstractDiscount
{
    /**
     * The instance of the discount.
     *
     * @var Discount
     */
    public Discount $discount;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->discount = new Discount([
            'priority' => 1,
            'type' => AmountOff::class,
            'starts_at' => now()->startOfHour(),
            'data' => [],
        ]);

        $this->currency = Currency::getDefault();
        $this->syncAvailability();

        $this->selectedBrands = collect();
        $this->selectedCollections = collect();
        $this->selectedProducts = collect();
    }

    /**
     * {@inheritDoc}.
     */
    public function rules()
    {
        $rules = array_merge([
            'discount.name' => 'required|unique:'.Discount::class.',name',
            'discount.handle' => 'required|unique:'.Discount::class.',handle',
            'discount.stop' => 'nullable',
            'discount.max_uses' => 'nullable|numeric|min:0',
            'discount.max_uses_per_user' => 'nullable|numeric|min:0',
            'discount.priority' => 'required|min:1',
            'discount.starts_at' => 'date',
            'discount.coupon' => 'nullable',
            'discount.ends_at' => 'nullable|date|after:starts_at',
            'discount.type' => 'string|required',
            'discount.data' => 'array',
            'selectedCollections' => 'array',
            'selectedBrands' => 'array',
        ], $this->getDiscountComponent()->rules());

        foreach ($this->currencies as $currency) {
            $rules['discount.data.min_prices.'.$currency->code] = 'nullable';
        }

        return $rules;
    }

    /**
     * Handler for when the discount name is updated.
     *
     * @param  string  $val
     * @return void
     */
    public function updatedDiscountName($val)
    {
        if (! $this->discount->handle) {
            $this->discount->handle = Str::snake(strtolower($val));
        }
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.discounts.create')
            ->layout('adminhub::layouts.app');
    }
}
