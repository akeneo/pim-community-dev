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
        if (!in_array($operator, ['is empty', 'is not empty'])) {
            list($numericValue, $unitValue) = explode(' ', $value);

            $this->find('css', 'input[name="value"]')->setValue($numericValue);

            $unitDropdown->click();
            $this->spin(function () use ($unitDropdown, $unitValue) {
                $unitDropdown->getParent()->find(
                    'css', sprintf('.dropdown-menu .choice_value:contains("%s")', $unitValue)
                )->click();

                return true;
            }, sprintf('Cannot click on the unit dropdown for value "%s"', $unitValue));
        }

        // We submit the filter
        $this->spin(function () {
            if (!$this->find('css', '.filter-criteria')->isVisible()) {
                return true;
            }
            $this->find('css', '.filter-update')->click();

            return false;
        }, 'Cannot update the filter');
    }
}
