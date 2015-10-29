<?php

namespace Context\Page\Category;

use Behat\Mink\Element\NodeElement;
use Context\Page\Base\Form;

/**
 * Abstract page view for categories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class CategoryView extends Form
{
    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Category tree'    => ['css' => '#tree'],
                'Tree select'      => ['css' => '#tree_select'],
                'Right click menu' => ['css' => '#vakata-contextmenu'],
            ]
        );
    }

    /**
     * @param string $category
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function findCategoryInTree($category)
    {
        $elt = $this->spin(function () use ($category) {
            return $this->getElement('Category tree')->find('css', sprintf('li a:contains("%s")', $category));
        }, 10, sprintf('Unable to find category "%s" in the tree', $category));

        return $elt;
    }

    /**
     * @param string $category
     *
     * @return CategoryView
     */
    public function selectTree($category)
    {
        $this->getElement('Tree select')->selectOption($category);

        return $this;
    }

    /**
     * @param string $action
     *
     * @throws \InvalidArgumentException
     *
     * @return CategoryView
     */
    public function rightClickAction($action)
    {
        $elt = $this->getElement('Right click menu')->find('css', sprintf('li a:contains("%s")', $action));
        if (!$elt) {
            throw new \InvalidArgumentException(sprintf('Unable to find action "%s" in the menu', $action));
        }
        $elt->click();

        return $this;
    }

    /**
     * @param string $category
     *
     * @return CategoryView
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
     * @return CategoryView
     */
    public function dragCategoryTo($category1, $category2)
    {
        $category1 = $this->findCategoryInTree($category1);
        $category2 = $this->findCategoryInTree($category2);

        $this->dragElementTo($category1, $category2);

        return $this;
    }
}
