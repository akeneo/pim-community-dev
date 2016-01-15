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
     * @param string $category1
     * @param string $category2
     *
     * @return CategoryView
     */
    public function dragCategoryTo($category1, $category2)
    {
        //TODO need to be reworked
        $category1 = $this->jsTreeManipulator->findNodeInTree($this->getElement('Category tree'), $category1);
        $category2 = $this->jsTreeManipulator->findNodeInTree($this->getElement('Category tree'), $category2);

        $this->dragElementTo($category1, $category2);

        return $this;
    }
}
