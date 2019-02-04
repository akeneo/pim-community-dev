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
        }, 'Cannot find the criteria selector to open filter');

        $this->spin(function () use ($filter) {
            if ($this->hasClass('open-filter')) {
                return true;
            }

            $filter->click();

            return false;
        }, 'Cannot open the filter');
    }

    /**
     * Remove the filter from the grid
     */
    public function remove()
    {
        $removeFilter = $this->spin(function () {
            return $this->find('css', '.disable-filter');
        }, 'Cannot find the remove button.');

        $removeFilter->click();
    }

    /**
     * Returns the displayed criteria in the filter
     *
     * @return string
     */
    public function getCriteriaHint()
    {
        return trim($this->spin(function () {
            return $this->find('css', '.filter-criteria-hint');
        }, 'Can not find the criteria hint of the filter')->getText());
    }
}
