<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Pim\Behat\Decorator\ElementDecorator;

class StringDecorator extends ElementDecorator
{
    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $this->find('css', '.dropdown-toggle')->click();
        $this->find('css', sprintf('.dropdown-menu .choice_value:contains("%s")', $operator))->click();

        $field = $this->find('css', '.select-field');
        if ('in list' === $operator) {
            $field = $this->decorate($field, ['Pim\Behat\Decorator\Field\Select2Decorator']);
        }

        $field->setValue($value);

        $this->find('css', '.filter-update')->click();
    }
}
