<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * Decorator dedicated to price collection attribute type.
 */
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
        $operatorField = $this->spin(function () {
            return $this->find('css', '.select2-container.operator');
        }, 'Cannot find the price operator field');

        $operatorField = $this->decorate(
            $operatorField,
            [Select2Decorator::class]
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            list($data, $currency) = explode(' ', $value);

            $field = $this->spin(function () {
                return $this->find('css', '[name="filter-data"]');
            }, 'Cannot find the price data field');

            $field->setValue($data);
            $this->getSession()->executeScript(
                sprintf(
                    '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-data"]\').trigger(\'change\')',
                    $this->getAttribute('data-name'),
                    $this->getAttribute('data-type')
                )
            );

            $currencyField = $this->spin(function () {
                return $this->find('css', '.select2-container.currency');
            }, 'Cannot find the price currency field');

            $currencyField = $this->decorate(
                $currencyField,
                [Select2Decorator::class]
            );

            $currencyField->setValue($currency);
            $this->getSession()->executeScript(
                sprintf(
                    '$(\'.filter-item[data-name="%s"][data-type="%s"] '.
                    'select[name="filter-currency"]\').trigger(\'change\')',
                    $this->getAttribute('data-name'),
                    $this->getAttribute('data-type')
                )
            );
        }
    }
}
