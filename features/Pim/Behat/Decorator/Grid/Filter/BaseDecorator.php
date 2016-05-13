<?php

namespace Pim\Behat\Decorator\Grid\Filter;

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
        $filter = $this->spin(function () {
            return $this->find('css', '.filter-criteria-selector');
        }, 'Cannot open the filter');

        $filter->click();
    }

    /**
     * Remove the filter from the grid
     */
    public function remove()
    {

    }
}
