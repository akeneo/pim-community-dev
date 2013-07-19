<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Pim\Bundle\ProductBundle\Entity\Category;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryEdit extends Page
{
    protected $path = '/enrich/category-tree/edit/{id}';

    protected $elements = array(
        'Tabs' => array('css' => '#form-navbar'),
    );

    /**
     * Update the category
     */
    public function save()
    {
        $this->pressButton('Save');
    }

    /**
     * Click on one of the horizontal tab
     *
     * @param string $tab the displayed name of the tab link
     */
    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }

    public function getUrl(Category $category)
    {
        return str_replace('{id}', $category->getId(), $this->getPath());
    }
}

