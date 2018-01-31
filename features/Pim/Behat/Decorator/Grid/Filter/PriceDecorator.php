<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class PriceDecorator extends ElementDecorator
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
            ['Pim\Behat\Decorator\Grid\Filter\OperatorDecorator']
        );
        $currencyDropdown = $this->decorate(
            $this->find('css', '.currency *[data-toggle="dropdown"]'),
            ['Pim\Behat\Decorator\Grid\Filter\OperatorDecorator']
        );

        // Split '10.5 EUR' -> $data = 10.5; $currency = 'EUR'
        $value = '' !== $value ? explode(' ', $value) : [];
        switch (count($value)) {
            case 0:
            case 1:
                list($data, $currency) = ['', reset($value)];
                break;
            case 2:
                list($data, $currency) = $value;
                break;
            default:
                throw new \InvalidArgumentException('You must specify a currency and a value');
        }

        // Set the value:
        if ('' !== $data) {
            $this->find('css', 'input[name="value"]')->setValue($data);
        }

        $operatorDropdown->setValue($operator);
        $currencyDropdown->setValue($currency);

        // Update the filter
        $this->spin(function () {
            if (!$this->find('css', '.filter-criteria')->isVisible()) {
                return true;
            }
            $this->find('css', '.filter-update')->click();

            return false;
        }, 'Cannot update the filter');
    }
}
