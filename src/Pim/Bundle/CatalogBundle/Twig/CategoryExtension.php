<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Twig extension to render category from twig templates
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryExtension extends \Twig_Extension
{
    /**
     * @var CategoryManager
     */
    protected $categoryManager;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param CategoryManager $categoryManager
     * @param ProductManager  $productManager
     */
    public function __construct(
        CategoryManager $categoryManager,
        ProductManager $productManager
    ) {
        $this->categoryManager = $categoryManager;
        $this->productManager  = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'children_response'        => new \Twig_Function_Method($this, 'childrenResponse'),
            'children_tree_response'   => new \Twig_Function_Method($this, 'childrenTreeResponse'),
            'list_categories_response' => new \Twig_Function_Method($this, 'listCategoriesResponse'),
            'list_trees_response'      => new \Twig_Function_Method($this, 'listTreesResponse')
        );
    }

    /**
     * List root categories (trees) for jstree
     *
     * @param array   $trees
     * @param integer $selectedTreeId
     * @param boolean $includeSub
     *
     * @return array
     */
    public function listTreesResponse(array $trees, $selectedTreeId = null, $includeSub = false)
    {
        $return = array();
        foreach ($trees as $tree) {
            $return[] = $this->formatTree($tree, $selectedTreeId, $includeSub);
        }

        return $return;
    }

    /**
     * Format a list of categories
     *
     * @param array             $categories
     * @param CategoryInterface $selectedCategory
     * @param CategoryInterface $parent
     * @param boolean           $withProductCount
     * @param boolean           $includeSub
     *
     * @return array
     */
    public function childrenTreeResponse(
        array $categories,
        CategoryInterface $selectedCategory = null,
        CategoryInterface $parent = null,
        $withProductCount = null,
        $includeSub = false
    ) {
        $selectedIds = array($selectedCategory->getId());
        $result = $this->formatCategoriesFromArray($categories, $selectedIds, $withProductCount, $includeSub);

        if ($parent !== null) {
            $result = $this->formatCategory($parent, $selectedIds, $withProductCount, $includeSub, $result);
        }

        return $result;
    }

    /**
     * List categories and children
     *
     * @param array             $categories
     * @param CategoryInterface $parent
     * @param boolean           $withProductCount
     * @param boolean           $includeSub
     *
     * @return array
     */
    public function childrenResponse(
        array $categories,
        CategoryInterface $parent = null,
        $withProductCount = false,
        $includeSub = false
    ) {
        $result = $this->formatCategories($categories, array(), $withProductCount, $includeSub);

        if ($parent !== null) {
            $result = $this->formatCategory($parent, array(), $withProductCount, $includeSub, $result);
        }

        return $result;
    }

    /**
     * List categories
     *
     * @param array      $categories
     * @param Collection $selectedCategories
     *
     * @return array
     */
    public function listCategoriesResponse(array $categories, Collection $selectedCategories)
    {
        $selectedIds = array();
        foreach ($selectedCategories as $selectedCategory) {
            $selectedIds[] = $selectedCategory->getId();
        }

        return $this->formatCategoriesAndCount($categories, $selectedIds, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_category_extension';
    }

    /**
     * Format categories from an array
     *
     * @param array   $categories
     * @param array   $selectedIds
     * @param boolean $withProductCount
     * @param boolean $includeSub
     *
     * @return array
     */
    protected function formatCategoriesFromArray(
        array $categories,
        array $selectedIds,
        $withProductCount = false,
        $includeSub = false
    ) {
        $result = array();
        foreach ($categories as $category) {
            $result[] = $this->formatCategoryFromArray($category, $selectedIds, $withProductCount, $includeSub);
        }

        return $result;
    }

    /**
     * Format a category from an array
     * Returns an array formatted as:
     * array(
     *     'attr'     => array(
     *         'id' => 'node_' + <categoryId>
     *     ),
     *     'data'     => string, // the category label + count if needed
     *     'state'    => string, // the css classes for category state
     *     'children' => array() // the same array for children
     * )
     *
     * @param array   $category
     * @param array   $selectedIds
     * @param boolean $withProductCount
     * @param boolean $includeSub
     *
     * @return array
     */
    protected function formatCategoryFromArray(
        array $category,
        array $selectedIds,
        $withProductCount = false,
        $includeSub = false
    ) {
        $state = $this->defineCategoryStateFromArray($category, $selectedIds);
        $label = $this->getLabel($category['item'], $withProductCount, $includeSub);

        return array(
            'attr'     => array(
                'id' => 'node_'. $category['item']->getId()
            ),
            'data'     => $label,
            'state'    => $state,
            'children' =>
                $this->formatCategoriesFromArray($category['__children'], $selectedIds, $withProductCount, $includeSub)
        );
    }

    /**
     * Format a category from an array
     * Returns an array formatted as:
     * array(
     *     'attr'     => array(
     *         'id' => 'node_' + <categoryId>
     *     ),
     *     'data'     => string, // the category label + count if needed
     *     'state'    => string, // the css classes for category state
     * )
     *
     * @param CategoryInterface $category
     * @param array             $selectedIds
     * @param boolean           $withProductCount
     * @param boolean           $includeSub
     * @param array             $children
     *
     * @return array
     */
    protected function formatCategory(
        CategoryInterface $category,
        array $selectedIds = array(),
        $withProductCount = false,
        $includeSub = false,
        array $children = array()
    ) {
        $state = $this->defineCategoryState($category, false, $selectedIds);
        $label = $this->getLabel($category, $withProductCount, $includeSub);

        $result = array(
            'attr'  => array(
                'id' => 'node_'. $category->getId()
            ),
            'data'  => $label,
            'state' => $state
        );

        if (!empty($children)) {
            $result['children'] = $children;
        }

        return $result;
    }

    /**
     * Format a tree for jstree js plugin
     * Returns an array formated as:
     * array(
     *     'id'       => int,    // the tree id
     *     'label'    => string, // the tree label
     *     'selected' => boolean // predicate to know if the tree is selected or not
     * )
     *
     * @param CategoryInterface $tree
     * @param integer           $selectedTreeId
     * @param boolean           $includeSub
     *
     * @return array
     */
    protected function formatTree(CategoryInterface $tree, $selectedTreeId, $includeSub)
    {
        $label = $this->getLabel($tree, true, $includeSub);

        return array(
            'id' => $tree->getId(),
            'label' => $label,
            'selected' => ($tree->getId() === $selectedTreeId) ? 'true' : 'false'
        );
    }

    /**
     * Format categories
     *
     * @param array   $categories
     * @param array   $selectedIds
     * @param boolean $withProductCount
     * @param boolean $includeSub
     *
     * @return array
     */
    protected function formatCategories(
        array $categories,
        $selectedIds = array(),
        $withProductCount = false,
        $includeSub = false
    ) {
        $result = array();
        foreach ($categories as $category) {
            $result[] = $this->formatCategory($category, array(), $withProductCount, $includeSub);
        }

        return $result;
    }

    /**
     * Format categories counting selected children
     *
     * @param array $categories
     * @param array $selectedIds
     *
     * @return array
     */
    protected function formatCategoriesAndCount(array $categories, $selectedIds = array())
    {
        $result = array();
        foreach ($categories as $category) {
            $result[] = $this->formatCategoryAndCount($category, $selectedIds);
        }

        return $result;
    }

    /**
     * Format category and count selected children
     *
     * @param array $category
     * @param array $selectedIds
     *
     * @return array
     */
    protected function formatCategoryAndCount(array $category, array $selectedIds)
    {
        $children = $this->formatCategoriesAndCount($category['__children'], $selectedIds);

        $result = $this->formatCategoryFromArray($category, $selectedIds, false, false);
        $result['children'] = $children;

        // count children
        $selectedChildren = 0;
        foreach ($children as $child) {
            $selectedChildren += $child['selectedChildrenCount'];
            if (preg_match('/checked/', $child['state'])) {
                $selectedChildren++;
            }
        }
        $result['selectedChildrenCount'] = $selectedChildren;

        // set label in bold
        if ($selectedChildren > 0) {
            $result['data'] = sprintf('<strong>%s</strong>', $result['data']);
        }

        return $result;
    }

    /**
     * Returns category label with|without children count
     * including|excluding sub-categories
     *
     * @param CategoryInterface $category
     * @param boolean           $withCount
     * @param boolean           $includeSub
     *
     * @return string
     */
    protected function getLabel(CategoryInterface $category, $withCount = false, $includeSub = false)
    {
        $label = $category->getLabel();
        if ($withCount) {
            $label = $label .' ('. $this->countProducts($category, $includeSub) .')';
        }

        return $label;
    }

    /**
     * Count products for a category
     *
     * @param CategoryInterface $category
     * @param bool              $includeSub
     *
     * @return string
     */
    protected function countProducts(CategoryInterface $category, $includeSub)
    {
        return $this
            ->productManager
            ->getProductsCountInCategory($category, $includeSub);
    }

    /**
     * Define the state of a category
     *
     * @param CategoryInterface $category
     * @param boolean           $hasChild
     * @param array             $selectedIds
     *
     * @return string
     */
    protected function defineCategoryState(
        CategoryInterface $category,
        $hasChild = false,
        array $selectedIds = array()
    ) {
        $state = $category->hasChildren() ? 'closed' : 'leaf';

        if ($hasChild === true) {
            $state = 'open';
        }

        if (in_array($category->getId(), $selectedIds)) {
            $state .= ' toselect jstree-checked';
        }

        if ($category->isRoot()) {
            $state .= ' jstree-root';
        }

        return $state;
    }

    /**
     * Define category state from a category array
     * array(
     *     'item'       => CategoryInterface,
     *     '__children' => array()
     * )
     *
     * @param array $category
     * @param array $selectedIds
     *
     * @return string
     */
    protected function defineCategoryStateFromArray(array $category, $selectedIds = array())
    {
        $children = $category['__children'];
        $category = $category['item'];
        $hasChild = (count($children) > 0);

        return $this->defineCategoryState($category, $hasChild, $selectedIds);
    }
}
