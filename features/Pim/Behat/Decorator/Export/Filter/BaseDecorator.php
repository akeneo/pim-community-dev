<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * This decorator is dedicated to export filters. It's a shortcut to avoit to rework the whole grid page.
 */
class BaseDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Opens the filter
     */
    public function open()
    {
    }

    /**
     * Remove the filter from the grid
     */
    public function remove()
    {
    }
}
