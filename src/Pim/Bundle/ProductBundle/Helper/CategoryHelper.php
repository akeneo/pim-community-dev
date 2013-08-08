<?php

namespace Pim\Bundle\ProductBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\ProductBundle\Entity\Category;

/**
 * Category helper
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryHelper
{
    /**
     * Format in array category trees. The tree where is the select category
     * will be selected (attribute selected at "true" in the response)
     *
     * @param array    $trees
     * @param Category $selectCategory
     *
     * @return array
     * @static
     */
    public static function treesResponse($trees, Category $selectCategory = null)
    {
        $return = array();
        $selectedTreeId = -1;

        if ($selectCategory != null) {
            $selectedTreeId = $selectCategory->getRoot();
        }

        foreach ($trees as $i => $tree) {
            $selectedTree = false;

            if (($selectedTreeId == -1) && ($i == 0)
                || ($tree->getId() == $selectedTreeId) ) {
                $selectedTree = true;
            }

            $return[] = array(
                'id' => $tree->getId(),
                'title'  => $tree->getTitle(),
                'selected' => $selectedTree ? "true" : "false"
            );
        }

        return $return;
    }

    /**
     * Format categories list into simple array with data formatted
     * for JStree json_data plugin.
     *
     * @param array $categories        Data to format into an array
     * @param array $withProductsCount Add product count for each category in its title
     * @param array $parent            If not null, will include this node as a parent node of the data
     *
     * @return array
     * @static
     */
    public static function childrenResponse(array $categories, $withProductsCount = false, Category $parent = null)
    {
        $result = array();

        foreach ($categories as $category) {
            $title = $category->getTitle();

            if ($withProductsCount) {
                $title .= ' ('.$category->getProductsCount().')';
            }

            $result[] = array(
                'attr' => array(
                    'id' => 'node_'. $category->getId()
                ),
                'data'  => $title,
                'state' => static::getState($category)
            );
        }

        if ($parent != null) {
            $result = array(
                'attr' => array(
                    'id' => 'node_' . $parent->getId()
                ),
                'data' => $parent->getTitle(),
                'state' => static::getState($parent),
                'children' => $result
            );
        }

        return $result;
    }

    /**
     * Format categories tree into multi-dimensional arrays with attributes
     * needed by JStree json_data plugin.
     *
     * Optionnaly can generate a selected state for the provided selectCategory
     *
     * @param array    $categories        categories
     * @param Category $selectCategory    select category
     * @param array    $withProductsCount Add product count for each category in its title
     * @param Category $parent            parent
     *
     * @return array
     * @static
     */
    public static function childrenTreeResponse(
        array $categories,
        Category $selectCategory = null,
        $withProductsCount = false,
        Category $parent = null
    ) {
        $result = static::formatCategory($categories, $selectCategory, $withProductsCount);

        if ($parent != null) {
            $result = array(
                'attr' => array(
                    'id' => 'node_' . $parent->getId()
                ),
                'data' => $parent->getTitle(),
                'state' => static::getState($parent),
                'children' => $result
            );
        }

        return $result;
    }

    /**
     * Format a node with its children to the format expected by jstree
     *
     * @param array    $categories
     * @param Category $selectCategory
     * @param boolean  $withProductsCount
     *
     * @see http://www.jstree.com/documentation/json_data
     *
     * @return array
     * @static
     */
    protected static function formatCategory(
        array $categories,
        Category $selectCategory = null,
        $withProductsCount = false
    ) {
        $result = array();

        foreach ($categories as $category) {
            $state = 'leaf';

            if (count($category['__children']) > 0) {
                $state = 'open';
            } else {
                if ($category['item']->hasChildren()) {
                    $state = 'closed';
                }
            }

            if (($selectCategory != null) &&
                ($category['item']->getId() == $selectCategory->getId())) {
                $state .= ' toselect';
            }

            $title = $category['item']->getTitle();

            if ($withProductsCount) {
                $title .= ' ('.$category['item']->getProductsCount().')';
            }

            if ($category['item']->getParent() == null) {
                $state .= ' jstree-root';
            }

            $result[] = array(
                'attr' => array(
                    'id' => 'node_'. $category['item']->getId()
                ),
                'data'  => $title,
                'state' => $state,
                'children' => static::formatCategory($category['__children'], $selectCategory, $withProductsCount)
            );
        }

        return $result;

    }

    /**
     * Return the state of the category (leaf if no children, closed otherwise)
     *
     * @param Category $category
     *
     * @return string
     * @static
     */
    protected static function getState(Category $category)
    {
        $state = $category->hasChildren() ? 'closed' : 'leaf';

        if ($category->getParent() == null) {
            $state .= ' jstree-root';
        }

        return $state;
    }

    /**
     * Format product list
     *
     * @param ArrayCollection $products
     *
     * @return array
     * @static
     */
    public static function productsResponse($products)
    {
        $return = array();

        foreach ($products as $product) {
            $return[] = array(
                'id' => $product->getId(),
                'name' => $product->getSku(),
                'description' => $product->getSku()
            );
        }

        return $return;
    }

    /**
     * Format path with a list of ids from tree to node
     *
     * @param ArrayCollection $categoryPath
     *
     * @return multitype:integer
     * @static
     */
    public static function pathResponse($categoryPath)
    {
        $return = array();

        foreach ($categoryPath as $category) {
            $return[] = $category->getId();
        }

        return $return;
    }

    /**
     * @param array      $categories
     * @param Collection $selectedCategories
     *
     * @return Ambigous <multitype:, multitype:multitype:multitype:string  string multitype: unknown  >
     */
    public static function listCategoriesResponse(array $categories, Collection $selectedCategories = null)
    {
        $selectedIds = array();

        foreach ($selectedCategories as $selectedCategory) {
            $selectedIds[] = $selectedCategory->getId();
        }

        return static::formatCategoryAndCount($categories, $selectedIds, true);
    }

    /**
     * Format a node with its children to the format expected by jstree.
     * If count is true, the state will contain a count attribute representing
     * the number of selected children
     *
     * @param array   $categories
     * @param array   $selectedIds
     * @param boolean $count
     *
     * @see http://www.jstree.com/documentation/json_data
     *
     * @return array
     * @static
     */
    protected static function formatCategoryAndCount(array $categories, $selectedIds = null, $count = false)
    {
        $result = array();

        foreach ($categories as $category) {
            $state = 'leaf';

            if (count($category['__children']) > 0) {
                $state = 'open';
            } else {
                if ($category['item']->hasChildren()) {
                    $state = 'closed';
                }
            }

            if (in_array($category['item']->getId(), $selectedIds)) {
                $state .= ' jstree-checked';
            }

            $children = static::formatCategoryAndCount($category['__children'], $selectedIds, $count);

            $selectedChildren = 0;

            foreach ($children as $child) {
                $selectedChildren += $child['selectedChildrenCount'];
                if (preg_match('/checked/', $child['state'])) {
                    $selectedChildren ++;
                }
            }

            $title = $category['item']->getTitle();

            if ($selectedChildren > 0) {
                $title = '<strong>'.$title.'</strong>';
            }

            if ($category['item']->getParent() == null) {
                $state .= ' jstree-root';
            }

            $result[] = array(
                'attr' => array(
                    'id' => 'node_'. $category['item']->getId()
                ),
                'data'  => $title,
                'state' => $state,
                'children' => $children,
                'selectedChildrenCount' => $selectedChildren
            );
        }

        return $result;
    }
}
