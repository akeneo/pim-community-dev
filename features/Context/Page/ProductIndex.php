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
        'Categories tree'   => array('css' => '#tree'),
        'Products'          => array('css' => 'table.grid tbody'),
        'Dialog'            => array('css' => 'div.modal'),
    );

    public function clickNewProductLink()
    {
        $this->clickLink('New product');
    }

    public function findProductRow($sku)
    {
        return $this->getElement('Products')->find('css', sprintf('tr:contains("%s")', $sku));
    }

    public function clickOnAction($sku, $action)
    {
        $row = $this->findProductRow($sku);

        $row->find('css', 'td.action-cell a.dropdown-toggle')->click();

        $element = $row->find('css', sprintf('a>i:contains("%s")', $action));

        if (!$element) {
            throw new \Exception(sprintf('Could not find action "%s".', $action));
        }

        $element->click();
    }

    public function confirmRemoval()
    {
        $element = $this->getElement('Dialog');

        if (!$element) {
            throw new \Exception('Could not find dialog window');
        }

        $button = $element->find('css', 'a.btn.ok');

        if (!$button) {
            throw new \Exception('Could not find confirmation button');
        }

        $button->click();
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
