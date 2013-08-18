<?php

namespace Context\Page\Category\Tree;

use Context\Page\Base\Form;

/**
 * Category tree creation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $path = '/enrich/category-tree/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Category tree'    => array('css' => '#tree'),
                'Right click menu' => array('css' => '#vakata-contextmenu'),
            )
        );
    }

    /**
     * @param string $category
     *
     * @return NodeElement
     */
    public function findCategoryInTree($category)
    {
        $elt = $this->getElement('Category tree')->find('css', sprintf('li a:contains(%s)', $category));

        if (!$elt) {
            throw new \InvalidArgumentException(sprintf('Unable to find category "%s" in the tree', $category));
        }

        return $elt;
    }

    /**
     * @param string $action
     *
     * @return Creation
     */
    public function rightClickAction($action)
    {
        $elt = $this->getElement('Right click menu')->find('css', sprintf('li a:contains(%s)', $action));

        if (!$elt) {
            throw new \InvalidArgumentException(sprintf('Unable to find action "%s" in the menu', $action));
        }

        $elt->click();

        return $this;
    }

    /**
     * @param string $category
     *
     * @return Creation
     */
    public function expandCategory($category)
    {
        $category = $this->findCategoryInTree($category);

        $category->getParent()->find('css', 'ins')->click();

        return $this;
    }

    /**
     * @param string $category1
     * @param string $category2
     *
     * @return Creation
     */
    public function dragCategoryTo($category1, $category2)
    {
        $category1 = $this->findCategoryInTree($category1);
        $category2 = $this->findCategoryInTree($category2);

        $category1->dragTo($category2);

        return $this;
    }
}
