<?php

namespace Context\Page\Product;

use Context\Page\Base\Form;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $elements = array(
        'Activated locales' => array('css' => '#select2-drop'),
    );

    public function findField($field)
    {
        $label = $this->find('css', sprintf('#pim_product_product_create label:contains("%s")', $field));

        if (!$label) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form label ', 'value', $field
            );
        }

        $field = $label->getParent()->find('css', 'input');

        if (!$field) {
            throw new ElementNotFoundException(
                $this->getSession(), 'form field ', 'id|name|label|value', $field
            );
        }

        return $field;
    }

    public function selectActivatedLocale($locale)
    {
        $elt = $this
            ->getElement('Activated locales')
            ->find('css', sprintf('li:contains("%s")', $locale))
        ;

        if (!$elt) {
            throw new \Exception(sprintf('Could not find locale "%s".', $locale));
        }

        $elt->click();
    }
}
