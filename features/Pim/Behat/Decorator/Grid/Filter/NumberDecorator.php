<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Pim\Behat\Decorator\ElementDecorator;

class NumberDecorator extends ElementDecorator
{
    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $operator = 'empty' === $operator ? 'is empty' : $operator;

        $operatorDropdown = $this->find('css', '.dropdown-toggle');
        $operatorDropdown->click();
        $operatorChoices = $operatorDropdown->getParent()->findAll('css', '.dropdown-menu .choice_value');

        // We can't use contains("%s") here, as ">=" contains ">" too, the css selector is not strict enough,
        // we need to do a perfect match on the label
        foreach ($operatorChoices as $choice) {
            if ($operator === $choice->getText()) {
                $choice->click();
            }
        }

        if ('is empty' !== $operator) {
            $this->find('css', 'input[name="value"]')->setValue($value);
        }

        $this->find('css', '.filter-update')->click();
    }
}
