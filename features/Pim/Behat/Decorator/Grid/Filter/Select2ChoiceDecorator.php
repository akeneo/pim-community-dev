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
        $operator = ucfirst($operator);

        $field = $this->spin(function () {
            return $this->find('css', '.select-field');
        }, sprintf('Cannot find the value field for the filter "%s"', $this->getAttribute('data-name')));

        $field = $this->decorate($field, ['Pim\Behat\Decorator\Field\Select2Decorator']);
        $field->setValue($value);

        $this->find('css', '.dropdown-toggle')->click();
        $this->find('css', sprintf('.dropdown-menu .operator_choice:contains("%s")', $operator))->click();

        $this->find('css', '.filter-update')->click();
    }
}
