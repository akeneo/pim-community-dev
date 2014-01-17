<?php

namespace Pim\Bundle\DashboardBundle\Widget;

/**
 * Registry of widgets
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Registry
{
    /** @var array */
    protected $widgets = array();

    /**
     * Add a widget to the register
     *
     * @param string          $alias
     * @param WidgetInterface $widget
     */
    public function add($alias, WidgetInterface $widget)
    {
        $this->widgets[$alias] = $widget;
    }

    /**
     * Get a widget from the register
     *
     * @param string $alias
     *
     * @return null|WidgetInterface
     */
    public function get($alias)
    {
        return isset($this->widgets[$alias]) ? $this->widgets[$alias] : null;
    }
}
