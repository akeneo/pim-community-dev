<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Behat context page for locale creation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleCreation extends Page
{
    protected $path = '/configuration/locale/create';

    protected $elements = array(
        'container' => array('css' => 'div[id=container]')
    );

//     public function fillField($field, $value)
//     {
//         if ($field === 'Locale') {
//             $elt = $this->getElement('container')
//                         ->find('div[id=s2id_pim_config_locale_form_code]');
//             $elt->click();

//             $div = $this->find($selector, $locator)
//             $this->fillField('select')
//         }

// //         return parent::fillField($field, $value);
//     }
}
