<?php

namespace Akeneo\Platform\Bundle\DashboardBundle\Widget;

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
    protected $widgets = [];

    /**
     * Add a widget to the register
     *
     * @param WidgetInterface $widget
     * @param int             $position
     */
    public function add(WidgetInterface $widget, $position)
    {
        if (!isset($this->widgets[$position])) {
            $this->widgets[$position] = $widget;
        } else {
            $this->add($widget, ++$position);
        }
        ksort($this->widgets);
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
        foreach ($this->widgets as $widget) {
            if ($widget->getAlias() === $alias) {
                return $widget;
            }
        }

        return null;
    }

    /**
     * List available widgets
     *
     * @return WidgetInterface[]
     */
    public function getAll()
    {
        return $this->widgets;
    }
}
