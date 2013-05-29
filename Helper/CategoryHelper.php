<?php
namespace Pim\Bundle\ProductBundle\Helper;

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
     * Format in array category trees
     *
     * @param ArrayCollection $trees
     *
     * @return array
     * @static
     */
    public static function treesResponse($trees)
    {
        $return = array();

        foreach ($trees as $tree) {
            $return[] = array(
                'id' => $tree->getId(),
                'title'  => $tree->getTitle()
            );
        }

        return $return;
    }

    /**
     * Format in array content category
     *
     * @param ArrayCollection $categories
     *
     * @return array
     * @static
     */
    public static function childrenResponse($categories)
    {
        $return = array();

        foreach ($categories as $category) {
            $return[] = array(
                'attr' => array(
                    'id' => 'node_'. $category->getId()
                ),
                'data'  => $category->getTitle(),
                'state' => static::getState($category)
            );
        }

        return $return;
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
        return $category->hasChildren() ? 'closed' : 'leaf';
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
}
