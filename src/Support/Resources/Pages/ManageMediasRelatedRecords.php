<?php

namespace Lunar\Admin\Support\Resources\Pages;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager;

class ManageMediasRelatedRecords extends BaseManageRelatedRecords
{
    protected static string $relationship = 'media';

    public function getTitle(): string
    {
        return __('lunarpanel::relationmanagers.medias.title_plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::media');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::relationmanagers.medias.title_plural');
    }

    public function getRelationManagers(): array
    {
        $mediaCollections = $this->getOwnerRecord()->getRegisteredMediaCollections();

        $relationManagers = [];

        foreach ($mediaCollections as $mediaCollection) {
            $relationManagers[] = MediaRelationManager::make([
                'mediaCollection' => $mediaCollection->name,
            ]);
        }

        return [
            RelationGroup::make('Media', $relationManagers),
        ];
    }
}
