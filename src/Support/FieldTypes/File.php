<?php

namespace Lunar\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Lunar\Admin\Support\Synthesizers\FileSynth;
use Lunar\Models\Attribute;

class File extends BaseFieldType
{
    protected static string $synthesizer = FileSynth::class;

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return FileUpload::make($attribute->handle)
            ->when(filled($attribute->validation_rules), fn (FileUpload $component) => $component->rules($attribute->validation_rules))
            ->required((bool) $attribute->configuration->get('required'))
            ->helperText($attribute->translate('description'));
    }
}
