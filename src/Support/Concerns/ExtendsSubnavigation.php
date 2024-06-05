<?php

namespace Lunar\Admin\Support\Concerns;

use Filament\Pages\Page;

trait ExtendsSubnavigation
{
    public static function getRecordSubNavigation(Page $page): array
    {
        $pages = self::callLunarHook('extendSubNavigation', static::getDefaultSubnavigation());

        return $page->generateNavigationItems($pages);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [];
    }
}
