<?php

namespace Context\Page\Product;

use Context\Page\Base\Grid;

/**
 * Product index page
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
    protected $path = '/enrich/product/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Categories tree'  => array('css' => '#tree'),
                'Tree select'      => array('css' => '#tree_select'),
                'Locales dropdown' => array('css' => '#locale-switcher'),
            )
        );
    }

    /**
     * @param string $locale
     */
    public function switchLocale($locale)
    {
        $elt = $this->getElement('Locales dropdown')->find('css', 'span.dropdown-toggle');
        if (!$elt) {
            throw new \Exception('Could not find locale switcher.');
        }
        $elt->click();

        $elt = $this->getElement('Locales dropdown')->find('css', sprintf('a[title=%s]', $locale));
        if (!$elt) {
            throw new \Exception(sprintf('Could not find locale "%s" in switcher.', $locale));
        }
        $elt->click();
    }

    /**
     * @param string $category
     *
     * @return Index
     */
    public function selectTree($category)
    {
        $this->getElement('Tree select')->selectOption($category);

        return $this;
    }

    /**
     * @param Category $category
     */
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

    /**
     * Filter by unclassified products
     */
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

    /**
     * Press the mass edit button
     */
    public function massEdit()
    {
        $this->pressButton('Mass Edition');
    }

    /**
     * Press the mass delete button
     */
    public function massDelete()
    {
        $this->pressButton('Delete');
    }
}
