<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Pim\Behat\Decorator\ElementDecorator;

class BaseDecorator extends ElementDecorator
{
    /**
     * Opens the filter
     */
    public function open()
    {
        $this->find('css', '.filter-criteria-selector')->click();
    }

    /**
     * Remove the filter from the grid
     */
    public function remove()
    {

    }
}
