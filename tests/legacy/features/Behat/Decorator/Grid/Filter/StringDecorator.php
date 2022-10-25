<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

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
            $this->element->find('css', '*[data-toggle="dropdown"]'),
            [OperatorDecorator::class]
        );
        $operatorDropdown->setValue($operator);

        if (!in_array($operator, ['is empty', 'is not empty'])) {
            $field = $this->spin(function () {
                return $this->element->find('css', '.select-field');
            }, sprintf('Cannot find the value field for the filter "%s"', $this->element->getAttribute('data-name')));

            if ('in list' === $operator) {
                $field = $this->decorate($field, [Select2Decorator::class]);
            }

            $field->setValue($value);
        }

        $this->spin(function () {
            if (!$this->element->find('css', '.filter-criteria')->isVisible()) {
                return true;
            }
            $this->element->find('css', '.filter-update')->click();

            return false;
        }, 'Cannot update the filter');
    }
}
