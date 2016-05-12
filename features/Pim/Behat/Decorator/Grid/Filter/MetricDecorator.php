<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class MetricDecorator extends ElementDecorator
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
        $dropdowns = $this->findAll('css', '.dropdown-toggle');

        // We set the operator
        $operatorDropdown = $this->decorate(
            $dropdowns[0],
            ['Pim\Behat\Decorator\Grid\Filter\OperatorDecorator']
        );
        $operatorDropdown->setValue($operator);

        $unitDropdown = $dropdowns[1];

        // We set the value
        if ('is empty' !== $operator) {
            list($numericValue, $unitValue) = explode(' ', $value);

            $this->find('css', 'input[name="value"]')->setValue($numericValue);

            $unitDropdown->click();
            $unitDropdown->getParent()->find(
                'css', sprintf('.dropdown-menu .choice_value:contains("%s")', $unitValue)
            )->click();
        }

        // We submit the filter
        $this->spin(function () {
            $this->find('css', '.filter-update')->click();

            return true;
        }, 'Cannot update the filter');
    }
}
