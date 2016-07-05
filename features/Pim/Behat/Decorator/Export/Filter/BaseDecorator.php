<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

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
