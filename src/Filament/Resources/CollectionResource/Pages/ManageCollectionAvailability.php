<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\ProductResource\RelationManagers\CustomerGroupRelationManager;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;

class ManageCollectionAvailability extends BaseManageRelatedRecords
{
    protected static string $resource = CollectionResource::class;

    protected static string $relationship = 'channels';

    public function getTitle(): string
    {
        return __('lunarpanel::product.pages.availability.label');
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = static::getResource()::getCollectionBreadcrumbs($this->getRecord());

        $crumbs[] = $this->getBreadcrumb();

        return $crumbs;
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::availability');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.availability.label');
    }

    public function getRelationManagers(): array
    {
        return [
            RelationGroup::make('Availability', [
                ChannelRelationManager::class,
                CustomerGroupRelationManager::make([
                    'pivots' => [
                        'enabled',
                        'visible',
                    ],
                ]),
            ]),
        ];
    }
}
