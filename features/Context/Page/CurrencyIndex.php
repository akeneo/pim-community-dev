<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyIndex extends Page
{
    protected $path = '/configuration/currency/';

    protected $elements = array(
        'Currencies' => array('css' => 'table.grid'),
    );

    public function findActivatedCurrency($currency)
    {
        return $this
            ->getElement('Currencies')
            ->find('css', sprintf('tr:contains("%s")', $currency))
            ->find('css', 'span.label-success')
        ;
    }

    public function findDeactivatedCurrency($currency)
    {
        return $this
            ->getElement('Currencies')
            ->find('css', sprintf('tr:contains("%s")', $currency))
            ->find('css', 'span.label-important')
        ;
    }
}

