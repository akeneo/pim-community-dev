<?php

namespace Pim\Bundle\CatalogBundle\Twig;

use Doctrine\Common\Collections\Collection;

use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
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
     * Constructor
     *
     * @param CategoryManager $categoryManager
     */
    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'list_trees_response'      => new \Twig_Function_Method($this, 'listTreesResponse'),
            'children_response'      => new \Twig_Function_Method($this, 'childrenResponse'),
            'children_tree_response'      => new \Twig_Function_Method($this, 'childrenTreeResponse'),
            'list_categories_response' => new \Twig_Function_Method($this, 'listCategoriesResponse'),
            'list_products'    => new \Twig_Function_Method($this, 'listProducts')
        );
    }

    /**
     * List root categories (trees) for jstree
     *
     * @param array $trees
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

    public function childrenTreeResponse(
        array $categories,
        CategoryInterface $selectedCategory = null,
        CategoryInterface $parent = null,
        $withProductCount = null,
        $includeSub = false
    ) {
        $result = $this->formatCategoriesFromArray($categories, $selectedCategory, $withProductCount, $includeSub);

        if ($parent !== null) {
            $result = array(
                'attr' => array(
                    'id' => 'node_'. $parent->getId()
                ),
                'data' => $parent->getLabel(),
                'state' => $this->defineCategoryState($parent),
                'children' => $result
            );
        }

        return $result;
    }

    /**
     * Format categories from an array
     *
     * @param array             $categories
     * @param CategoryInterface $selectedCategory
     * @param boolean           $withProductCount
     * @param boolean           $includeSub
     *
     * @return array
     */
    protected function formatCategoriesFromArray(array $categories, CategoryInterface $selectedCategory, $withProductCount = false, $includeSub = false)
    {
        $result = array();

        foreach ($categories as $category) {
            $result[] = $this->formatCategoryFromArray($category, $selectedCategory, $withProductCount, $includeSub);
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
     * @param array $category
     * @param CategoryInterface $selectedCategory
     * @param boolean $withProductCount
     * @param boolean $includeSub
     *
     * @return array
     */
    protected function formatCategoryFromArray(array $category, CategoryInterface $selectedCategory, $withProductCount = false, $includeSub = false)
    {
        $state = $this->defineCategoryStateFromArray($category, array($selectedCategory->getId()));
        $label = $this->getLabel($category['item'], $withProductCount, $includeSub);

        return array(
            'attr'     => array(
                'id' => 'node_'. $category['item']->getId()
            ),
            'data'     => $label,
            'state'    => $state,
            'children' => $this->formatCategoriesFromArray($category['__children'], $selectedCategory, $withProductCount, $includeSub)
        );
    }

    public function childrenResponse(array $categories, Category $parent = null, $withProductsCount = false, $includeSub = false)
    {
        $result = array();

        foreach ($categories as $category) {
            $label = $this->getLabel($category, $withProductsCount, $includeSub);

            $result[] = array(
                'attr' => array(
                    'id' => 'node_'. $category->getId()
                ),
                'data' => $label,
                'state' => $this->defineCategoryState($category)
            );
        }

        if ($parent !== null) {
            $result = array(
                'attr' => array(
                    'id' => 'node_'. $parent->getId()
                ),
                'data' => $this->getLabel($parent),
                'state' => $this->defineCategoryState($parent),
                'children' => $result
            );
        }

        return $result;
    }

    public function listCategoriesResponse(array $categories, Collection $selectedCategories)
    {
        $selectedIds = array();

        foreach ($selectedCategories as $selectedCategory) {
            $selectedIds[] = $selectedCategory->getId();
        }

        return $this->formatCategoriesAndCount($categories, $selectedIds, true);
    }

    protected function formatCategoriesAndCount(array $categories, $selectedIds = array(), $count = false)
    {
        $result = array();

        foreach ($categories as $category) {
            $children = $this->formatCategoriesAndCount($category['__children'], $selectedIds, $count);

            $selectedChildren = 0;
            foreach ($children as $child) {
                $selectedChildren += $child['selectedChildrenCount'];
                if (preg_match('/checked/', $child['state'])) {
                    $selectedChildren++;
                }
            }

            $label = $this->getLabel($category['item']);
            if ($selectedChildren > 0) {
                $label = sprintf('<strong>%s</strong>', $label);
            }

            $result[] = array(
                'attr' => array(
                    'id' => sprintf('node_%s', $category['item']->getId())
                ),
                'data' => $label,
                'state' => $this->defineCategoryStateFromArray($category, $selectedIds),
                'children' => $children,
                'selectedChildrenCount' => $selectedChildren
            );
        }

        return $result;
    }

    /**
     * Returns category label with(out?) count and can include sub-categories
     *
     * @param CategoryInterface $category
     * @param boolean           $count      predicate to add the count or not
     * @param boolean           $includeSub include sub-categories for the count
     *
     * @return string
     */
    protected function getLabel(CategoryInterface $category, $count = false, $includeSub = false)
    {
        $label = $category->getLabel();
        if ($count) {
            $label = $label .' ('. $this->countProducts($category, $includeSub) .')';
        }

        return $label;
    }

    /**
     * Count products for a category
     *
     * @param CategoryInterface $category
     * @param bool              $nested
     *
     * @return string
     */
    protected function countProducts(CategoryInterface $category, $nested)
    {
        return $this
            ->categoryManager
            ->getEntityRepository()
            ->countProductsLinked($category, !$nested);
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
    protected function defineCategoryState(CategoryInterface $category, $hasChild = false, array $selectedIds = array())
    {
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_category_extension';
    }

    /**
     * List products for jstree
     *
     * @param array $products
     *
     * @return array
     */
    public function listProducts(array $products)
    {
        $productsList = array();
        foreach ($products as $product) {
            $productsList[] = $this->formatProduct($product);
        }

        return $productsList;
    }

    /**
     * Format a product interface for jstree
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function formatProduct(ProductInterface $product)
    {
        return array(
            'id'          => $product->getId(),
            'name'        => $product->getIdentifier(),
            'description' => (string) $product
        );
    }
}
