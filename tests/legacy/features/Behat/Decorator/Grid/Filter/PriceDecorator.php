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
    public function filter($operator, $value = '')
    {
        $currency = null;
        $amount = null;

        if ('' !== $value) {
            // Split '10.5 EUR' -> $data = 10.5;  $currency = 'EUR'
            // or    ' EUR'     -> $data = 'EUR'; $currency = null
            $result = explode(' ', $value);
            if (count($result) === 2) {
                $currency = $result[1];
                $amount = $result[0];
            } else {
                $currency = $result[0];
            }
        }

        $operatorDropdown = $this->decorate(
            $this->find('css', '.operator *[data-toggle="dropdown"]'),
            [OperatorDecorator::class]
        );
        $operatorDropdown->setValue($operator);

        if (null !== $currency) {
            $currencyDropdown = $this->decorate(
                $this->find('css', '.currency *[data-toggle="dropdown"]'),
                [OperatorDecorator::class]
            );
            $currencyDropdown->setValue($currency);
        }

        if (null !== $amount) {
            $this->find('css', 'input[name="value"]')->setValue($amount);
        }

        // Update the filter
        $this->spin(
            function () {
                if (!$this->find('css', '.filter-criteria')->isVisible()) {
                    return true;
                }
                $this->find('css', '.filter-update')->click();

                return false;
            },
            'Cannot update the filter'
        );
    }
}
