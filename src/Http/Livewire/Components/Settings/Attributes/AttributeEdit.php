<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Attributes;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Lunar\Facades\FieldTypeManifest;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Http\Livewire\Traits\WithLanguages;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;

class AttributeEdit extends Component
{
    use Notifies;
    use WithLanguages;

    /**
     * The attribute group.
     *
     * @var string
     */
    public ?AttributeGroup $group = null;

    /**
     * The attribute instance.
     *
     * @var \Lunar\Models\Attribute
     */
    public ?Attribute $attribute = null;

    /**
     * Whether the panel should be visible.
     */
    public bool $panelVisible = true;

    /**
     * Whether the user has input a handle manually.
     */
    public bool $manualHandle = false;

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->attribute = $this->attribute ?: new Attribute([
            'searchable' => true,
            'filterable' => false,
            'required' => false,
            'section' => 'main',
            'system' => false,
            'type' => get_class($this->fieldTypes->first()),
        ]);

        if ($this->attribute->id) {
            $this->group = $this->attribute->attributeGroup;
        }
    }

    public function updatedPanelVisible($val)
    {
        $this->emit('attribute-edit.closed');
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        $rules = [
            'attribute.name' => 'required',
            'attribute.handle' => 'required',
            'attribute.required' => 'nullable|boolean',
            'attribute.searchable' => 'nullable|boolean',
            'attribute.filterable' => 'nullable|boolean',
            'attribute.configuration' => 'nullable|array',
            'attribute.section' => 'nullable|string',
            'attribute.system' => 'boolean',
            'attribute.type' => 'required',
            'attribute.validation_rules' => 'nullable|string',
        ];

        if (! $this->attribute->id) {
            $rules['attribute.handle'] = ['required', Rule::unique(Attribute::class, 'handle')->where(function ($query) {
                return $query->where('attribute_group_id', $this->group->id);
            })];
        }

        foreach ($this->languages as $lang) {
            $rules["attribute.name.{$lang->code}"] = ($lang->default ? 'required' : 'nullable').'|string|max:255';
        }

        if ($this->getFieldType()) {
            $fieldTypeOptions = $this->getFieldTypeConfig()['options'] ?? [];

            foreach ($fieldTypeOptions as $field => $validation) {
                $rules["attribute.configuration.{$field}"] = $validation;
            }
        }

        return $rules;
    }

    protected function validationAttributes()
    {
        $attributes = [];

        foreach ($this->languages as $lang) {
            $attributes["attribute.name.{$lang->code}"] = lang(key: 'inputs.name', locale: $lang->code, lower: true);
        }

        if ($this->getFieldType()) {
            $fieldTypeOptions = $this->getFieldTypeConfig()['options'] ?? [];

            foreach ($fieldTypeOptions as $field => $validation) {
                $attributes["attribute.configuration.{$field}"] = lang(key: "inputs.{$field}", locale: $this->defaultLanguage->code, lower: true);
            }
        }

        return $attributes;
    }

    /**
     * Return the available field types.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFieldTypesProperty()
    {
        return FieldTypeManifest::getTypes();
    }

    /**
     * Return the selected field type.
     */
    public function getFieldType()
    {
        return app()->make($this->attribute->type);
    }

    /**
     * Return the config for the field type.
     *
     * @return array|null
     */
    public function getFieldTypeConfig()
    {
        return $this->getFieldType()?->getConfig() ?: null;
    }

    /**
     * Format the handle on update to a slug.
     *
     * @return void
     */
    public function updatedAttributeHandle()
    {
        $this->attribute->handle = Str::handle($this->attribute->handle);
    }

    public function formatHandle()
    {
        if (! $this->manualHandle && ! $this->attribute->handle) {
            $this->attribute->handle = Str::handle(
                $this->attribute->name[$this->defaultLanguage->code] ?? null
            );
        }
    }

    /**
     * Save the attribute.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        if (! $this->attribute->id) {
            $this->attribute->attribute_type = $this->group->attributable_type;
            $this->attribute->attribute_group_id = $this->group->id;
            $this->attribute->position = Attribute::whereAttributeGroupId(
                $this->group->id
            )->count() + 1;
            $this->attribute->save();
            $this->notify(
                __('adminhub::notifications.attribute-edit.created')
            );
            $this->emit('attribute-edit.created', $this->attribute->id);

            return;
        }

        $this->attribute->save();

        $this->notify(
            __('adminhub::notifications.attribute-edit.updated')
        );
        $this->emit('attribute-edit.updated', $this->attribute->id);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.attributes.attribute-edit')
            ->layout('adminhub::layouts.base');
    }
}
