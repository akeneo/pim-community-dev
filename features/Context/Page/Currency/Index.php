<?php

namespace Context\Page\Currency;

use Context\Page\Base\Grid;

/**
 * Currency index page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
     * @param string $currency
     *
     * @return NodeElement
     */
    public function findActivatedCurrency($currency)
    {
        return $this->getRow($currency)->find('css', 'span.label-success');
    }

    /**
     * @param string $currency
     *
     * @return NodeElement
     */
    public function findDeactivatedCurrency($currency)
    {
        return $this->getRow($currency)->find('css', 'span.label-important');
    }

    /**
     * @param string $currency
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findCurrency($currency)
    {
        return $this->getRow($currency);
    }

    /**
     * @param array $currencies
     */
    public function activateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->clickOnAction($currency, 'Toggle');
        }
    }

    /**
     * @param array $currencies
     */
    public function deactivateCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->clickOnAction($currency, 'Toggle');
        }
    }
}
