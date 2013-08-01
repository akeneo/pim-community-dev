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
        return $this->getGridRow($currency)->find('css', 'span.label-success');
    }

    public function findDeactivatedCurrency($currency)
    {
        return $this->getGridRow($currency)->find('css', 'span.label-important');
    }

    public function activateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->clickOnAction($currency, 'Toggle');
        }
    }

    public function deactivateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->clickOnAction($currency, 'Toggle');
        }
    }
}
