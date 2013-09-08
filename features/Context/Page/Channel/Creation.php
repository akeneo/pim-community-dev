<?php

namespace Context\Page\Channel;

use Context\Page\Base\Form;

/**
 * Channel creation page
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $path = '/configuration/channel/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Locales'    => array('css' => '#pim_catalog_channel_form_locales'),
                'Currencies' => array('css' => '#pim_catalog_channel_form_currencies'),
            )
        );
    }

    /**
     * Select the locale
     * @param string $locale
     */
    public function selectLocale($locale)
    {
        $this->getElement('Locales')->selectOption($locale);
    }

    /**
     * Select the currency
     * @param string $currency
     */
    public function selectCurrency($currency)
    {
        $this->getElement('Currencies')->selectOption($currency);
    }
}
