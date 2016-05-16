<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class Select2ChoiceDecorator extends ElementDecorator
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
        $field = $this->spin(function () {
            return $this->find('css', '.select-field');
        }, sprintf('Cannot find the value field for the filter "%s"', $this->getAttribute('data-name')));

        $field = $this->decorate($field, ['Pim\Behat\Decorator\Field\Select2Decorator']);
        $field->close();

        if ('is empty' !== $operator) {
            // We close then reopen the widget to be sure we are always in the same state
            $field->open();
            $field->setValue($value);
        }

        $operatorDropdown = $this->find('css', '.dropdown-toggle');
        if (null !== $operatorDropdown) {
            $operatorDropdown = $this->decorate(
                $operatorDropdown,
                ['Pim\Behat\Decorator\Grid\Filter\OperatorDecorator']
            );
            $operatorDropdown->setValue($operator);
        }

        $this->spin(function () {
            $this->find('css', '.filter-update')->click();

            return true;
        }, 'Cannot update the filter');
    }
}
