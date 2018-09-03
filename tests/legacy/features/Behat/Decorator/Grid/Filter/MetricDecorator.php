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
        $operatorDropdown = $this->decorate(
            $this->find('css', '.operator *[data-toggle="dropdown"]'),
            [OperatorDecorator::class]
        );
        $unitDropdown = $this->decorate(
            $this->find('css', '.unit *[data-toggle="dropdown"]'),
            [OperatorDecorator::class]
        );

        // We set the value
        if (!in_array($operator, ['is empty', 'is not empty'])) {
            list($numericValue, $unitValue) = explode(' ', $value);

            $this->find('css', 'input[name="value"]')->setValue($numericValue);
            $unitDropdown->setValue($unitValue);
        }

        $operatorDropdown->setValue($operator);

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
