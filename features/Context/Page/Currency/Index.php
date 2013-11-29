<?php

namespace Context\Page\Currency;

use Context\Page\Base\Grid;

/**
 * Currency index page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /**
     * @var string $path
     */
    protected $path = '/configuration/currency/';

    /**
     * @param array $currencies
     */
    public function activateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->clickOnAction($currency, 'Change status');
        }
    }

    /**
     * @param array $currencies
     */
    public function deactivateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->clickOnAction($currency, 'Change status');
        }
    }
}
