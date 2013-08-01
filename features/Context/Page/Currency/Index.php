<?php

namespace Context\Page\Currency;

use Context\Page\Base\Grid;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    protected $path = '/configuration/currency/';

    public function findActivatedCurrency($currency)
    {
        return $this
            ->getCurrencyRow($currency)
            ->find('css', 'span.label-success')
        ;
    }

    public function findDeactivatedCurrency($currency)
    {
        return $this
            ->getCurrencyRow($currency)
            ->find('css', 'span.label-important')
        ;
    }

    public function activateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->toggle($currency);
        }
    }

    public function deactivateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->toggle($currency);
        }
    }

    private function getCurrencyRow($currency)
    {
        $currencyRow = $this
            ->getElement('Grid content')
            ->find('css', sprintf('tr:contains("%s")', $currency))
        ;

        if (!$currencyRow) {
            throw new \InvalidArgumentException(sprintf(
                'Couldn\'t find a row for currency "%s"', $currency
            ));
        }

        return $currencyRow;
    }

    private function toggle($currency)
    {
        $currencyRow = $this->getCurrencyRow($currency);
        $currencyRow->find('css', 'a.dropdown-toggle')->click();
        $currencyRow->clickLink('Toggle');
    }
}
