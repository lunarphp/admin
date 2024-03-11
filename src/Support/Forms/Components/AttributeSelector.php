<?php

namespace Lunar\Admin\Support\Forms\Components;

use Filament\Forms\Components\CheckboxList;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

class AttributeSelector extends CheckboxList
{
    protected string $view = 'lunarpanel::forms.components.attribute-selector';

    protected ?string $attributableType = null;

    public function withType($type)
    {
        $this->attributableType = $type;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadStateFromRelationships();
    }

    public function relationship(string|\Closure $name = null, string|\Closure $titleAttribute = null, \Closure $modifyQueryUsing = null): static
    {
        parent::relationship($name, $titleAttribute, $modifyQueryUsing);

        $type = $this->attributableType;

        $this->saveRelationshipsUsing(static function (CheckboxList $component, ?array $state) use ($type) {
            // Get all current mapped attributes
            $existing = $component->getRelationship()->get();

            // Filter out any that match this attribute type but are not in the saved state.
            $attributes = $existing->reject(
                fn ($attribute) => ! in_array($attribute->id, $state ?? []) && $attribute->attribute_type == $type
            )->pluck('id')->unique()->merge($state)->toArray();

            $component->getRelationship()->sync($attributes);
        });

        return $this;
    }

    public function getAttributeGroups()
    {
        $type = get_class(
            $this->getRelationship()->getParent()
        );

        if ($type === ProductType::class) {
            $type = Product::class;
        }

        if ($this->attributableType) {
            $type = $this->attributableType;
        }

        return AttributeGroup::whereAttributableType($type)->get();
    }

    public function getSelectedAttributes($groupId)
    {
        return Attribute::where('attribute_group_id', $groupId)->whereIn('id', $this->getState())->get();
    }

    public function getAttributes($groupId)
    {
        return Attribute::where('attribute_group_id', $groupId)->get();
    }
}
