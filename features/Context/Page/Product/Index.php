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
            )
        );
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
}
