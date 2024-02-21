<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class NumberDecorator extends ElementDecorator
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
            [OperatorDecorator::class]
        );
        $operatorDropdown->setValue($operator);

        if (!in_array($operator, ['is empty', 'is not empty'])) {
            $this->find('css', 'input[name="value"]')->setValue($value);
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
