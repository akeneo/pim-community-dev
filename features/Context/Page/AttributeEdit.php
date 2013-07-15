<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeEdit extends Page
{
    protected $path = '/enrich/product-attribute/edit/{id}';

    protected $elements = array(
        'Tabs' => array('css' => '#form-navbar'),
    );

    public function setPosition($position)
    {
        $this->fillField('Position', $position);

        return $this;
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }
}
