<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Pim\Behat\Decorator\ElementDecorator;

class MetricDecorator extends ElementDecorator
{
    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $dropdowns = $this->findAll('css', '.dropdown-toggle');

        $operatorDropdown = $dropdowns[0];
        $unitDropdown = $dropdowns[1];

        $operator = 'empty' === $operator ? 'is empty' : $operator;

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
            list($numericValue, $unitValue) = explode(' ', $value);

            $this->find('css', 'input[name="value"]')->setValue($numericValue);

            $unitDropdown->click();
            $unitDropdown->getParent()->find(
                'css', sprintf('.dropdown-menu .choice_value:contains("%s")', $unitValue)
            )->click();
        }

        $this->find('css', '.filter-update')->click();
    }
}
