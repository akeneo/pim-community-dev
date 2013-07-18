<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndex extends Page
{
    protected $path = '/enrich/product/';

    protected $elements = array(
        'Activated locales' => array('css' => '#select2-drop'),
        'Categories tree'   => array('css' => '#tree'),
        'Products'          => array('css' => 'table.grid'),
    );

    public function clickNewProductLink()
    {
        $this->clickLink('New product');
    }

    public function findProductRow($sku)
    {
        return $this->getElement('Products')->find('css', sprintf('tr:contains("%s")', $sku));
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

    public function clickCategoryFilterLink($category)
    {
        $elt = $this
            ->getElement('Categories tree')
            ->find('css', sprintf('#node_%s a', $category->getId()));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find category filter "%s".', $category->getId()));
        }

        $elt->click();
    }

    public function clickUnclassifiedCategoryFilterLink()
    {
        $elt = $this
            ->getElement('Categories tree')
            ->find('css', sprintf('#node_0 a'));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find unclassified category filter.'));
        }

        $elt->click();
    }
}
