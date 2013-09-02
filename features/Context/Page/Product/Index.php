<?php

namespace Context\Page\Product;

use Context\Page\Base\Grid;

/**
 * Product index page
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
                'Categories tree' => array('css' => '#tree'),
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
     * @param string $code
     */
    public function filterPerFamily($code)
    {
        $elt = $this->getElement('Filters')->find('css', sprintf(':contains("%s") select', $code));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find filter for family "%s".', $code));
        }

        $elt->selectOption($code);
    }

    /**
     * @param string $code
     */
    public function filterPerPrice($value, $currency)
    {
        $filter = $this->getFilter('Price');

        if (!$filter) {
            throw new \Exception('Could not find filter for price.');
        }

        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');

        $criteriaElt->fillField('value', $value);

        // Open the dropdown menu with currency list
        $this->pressButton('Currency');

        // Click on the Euro line in the currency menu
        $this->pressButton('EUR');

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $code
     */
    public function filterPerChannel($code)
    {
        $elt = $this->getElement('Filters')->find('css', sprintf(':contains("%s") select', $code));

        if (!$elt) {
            throw new \Exception(sprintf('Could not find filter for channel "%s".', $code));
        }

        $elt->selectOption($code);
    }
}
