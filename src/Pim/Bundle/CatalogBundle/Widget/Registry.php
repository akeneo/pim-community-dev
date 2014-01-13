<?php

namespace Pim\Bundle\CatalogBundle\Widget;

class Registry
{
    /** @var array */
    protected $widgets = array();

    public function add($name, WidgetInterface $widget)
    {
        $this->widgets[$name] = $widget;
    }

    public function get($name)
    {
        return isset($this->widgets[$name]) ? $this->widgets[$name] : null;
    }
}
