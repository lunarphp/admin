<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\Builders\ProductVariantsTableBuilder;
use Lunar\LivewireTables\Components\Columns\ImageColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;
use Lunar\Models\Product;

class ProductVariantsTable extends Table
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    protected $tableBuilderBinding = ProductVariantsTableBuilder::class;

    /**
     * {@inheritDoc}
     */
    public bool $searchable = false;

    /**
     * {@inheritDoc}
     */
    public bool $canSaveSearches = false;

    /**
     * {@inheritDoc}
     */
    public bool $filterable = false;

    public Product $product;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            ImageColumn::make('thumbnail', function ($record) {
                if (! $thumbnail = $record->getThumbnail()) {
                    return null;
                }

                return $thumbnail->getUrl('small');
            })->heading(false),
            TextColumn::make('sku')->url(function ($record) {
                return route('hub.products.variants.show', [
                    'product' => $this->product,
                    'variant' => $record,
                ]);
            }),
            TextColumn::make('options', function ($record) {
                return $record->values->map(function ($value) {
                    return $value->translate('name');
                })->join(' / ');
            }),

            TextColumn::make('price', function ($record) {
                $price = $record->basePrices->first(fn ($price) => $price->currency->default);

                return $price->price->formatted ?? 0;
            }),
            TextColumn::make('stock'),
            TextColumn::make('backorder'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $query = $this->query;

        return $this->tableBuilder
            ->product($this->product)
            ->perPage($this->perPage)
            ->getData();
    }
}
