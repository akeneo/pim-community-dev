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

        $dropdowns = $this->findAll('css', '.dropdown-toggle');

        $operatorDropdown = $dropdowns[0];
        $currencyDropdown = $dropdowns[1];

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
                throw \InvalidArgumentException('You must specify a currency and a value');
                break;
        }

        // Set the value:
        $this->find('css', 'input[name="value"]')->setValue($data);

        $currencyDropdown->click();

        $currencyChoice = $currencyDropdown->getParent()->find(
            'css', sprintf('.dropdown-menu .choice_value[data-value="%s"]', $currency)
        );
        if (null === $currencyChoice) {
            throw new \Exception(sprintf('Cannot find the choice for currency %s', $currency));
        }

        $currencyChoice->click();

        // Change the operator
        $operatorDropdown->click();
        $operatorChoices  = $operatorDropdown->getParent()->findAll('css', '.dropdown-menu .choice_value');

        // We can't use contains("%s") here, as ">=" contains ">" too, the css selector is not strict enough,
        // we need to do a perfect match on the label
        foreach ($operatorChoices as $choice) {
            if ($operator === $choice->getText()) {
                $choice->click();
            }
        }

        // Update the field
        $this->find('css', '.filter-update')->click();
    }
}
