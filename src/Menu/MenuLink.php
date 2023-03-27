<?php

namespace Lunar\Hub\Menu;

use Illuminate\Support\Str;
use Lunar\Hub\LunarHub;

class MenuLink implements MenuItem
{
    /**
     * The display name of the menu link.
     *
     * @var string
     */
    public $name;

    /**
     * The route name.
     *
     * @var string
     */
    public $route;

    /**
     * Reference to icon or full SVG.
     *
     * @var string
     */
    public $icon;

    /**
     * The handle for the menu link.
     *
     * @var string | array
     */
    public $handle;

    /**
     * Which authentication gate this checks for displaying.
     *
     * @var string
     */
    public $gate;

    /**
     * Reference to the livewire component to use.
     *
     * @var string
     */
    public $component;

    /**
     * Setter for the name property.
     *
     * @param  string  $name
     * @return static
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Setter for the handle property.
     *
     * @param  string | array  $handle
     * @return static
     */
    public function handle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * Setter for the gate property.
     *
     * @param  string  $gate
     * @return static
     */
    public function gate($gate)
    {
        $this->gate = $gate;

        return $this;
    }

    /**
     * Setter for the route property.
     *
     * @param  string  $route
     * @return static
     */
    public function route($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Setter for the component property.
     *
     * @param  string  $component
     * @return static
     */
    public function component($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Setter for the icon property.
     *
     * @param  string  $icon
     * @return static
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Render the HTML for the icon.
     *
     * @param  string  $attrs
     * @return string
     */
    public function renderIcon($attrs = null)
    {
        return LunarHub::icon($this->icon, $attrs);
    }

    /**
     * Determines whether this menu link is considered active.
     *
     * @param  string  $path
     * @return bool
     */
    public function isActive($path)
    {
        return (bool) collect($this->handle)->first(function ($handle) use ($path) {
            return Str::startsWith($path, $handle);
        });
    }
}
