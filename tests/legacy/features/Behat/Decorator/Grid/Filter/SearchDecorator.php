<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

class SearchDecorator extends ElementDecorator
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

    /**
     * Sets value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $field = $this->element->find('css', '[name="value"]');
        $field->setValue($value);
        $this->element->getSession()->executeScript(
            sprintf(
                '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="value"]\').trigger(\'change\')',
                $this->element->getAttribute('data-name'),
                $this->element->getAttribute('data-type')
            )
        );
    }

    /**
     * Return whether this filter input value is visible
     *
     * @return bool
     */
    public function isInputValueVisible()
    {
        try {
            $filterInput = $this->spin(function () {
                return $this->element->find('css', '[name="value"]');
            }, 'Cannot find the value input');
        } catch (TimeoutException $exception) {
            return false;
        }

        return $filterInput && $filterInput->isVisible();
    }

    /**
     * Search a value in the search filter
     * It dispatch a fake keydown event to run the search.
     *
     * @param string $value
     */
    public function search($value)
    {
        $this->element->setValue($value);
    }
}
