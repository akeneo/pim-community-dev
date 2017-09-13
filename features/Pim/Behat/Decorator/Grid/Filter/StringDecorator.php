<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class StringDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $operatorDropdown = $this->decorate(
            $this->find('css', '*[data-toggle="dropdown"]'),
            ['Pim\Behat\Decorator\Grid\Filter\OperatorDecorator']
        );
        $operatorDropdown->setValue($operator);

        if (!in_array($operator, ['is empty', 'is not empty'])) {
            $field = $this->spin(function () {
                return $this->find('css', '.select-field');
            }, sprintf('Cannot find the value field for the filter "%s"', $this->getAttribute('data-name')));

            if ('in list' === $operator) {
                $field = $this->decorate($field, ['Pim\Behat\Decorator\Field\Select2Decorator']);
            }

            $field->setValue($value);
        }

        $this->spin(function () {
            if (!$this->find('css', '.filter-criteria')->isVisible()) {
                return true;
            }
            $this->find('css', '.filter-update')->click();

            return false;
        }, 'Cannot update the filter');
    }
}
