<?php

namespace Pim\Bundle\CatalogBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Category helper
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryHelper
{
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
            $children = static::formatCategoryAndCount($category['__children'], $selectedIds, $count);

            $selectedChildren = 0;

            foreach ($children as $child) {
                $selectedChildren += $child['selectedChildrenCount'];
                if (preg_match('/checked/', $child['state'])) {
                    $selectedChildren ++;
                }
            }

            $label = $category['item']->getLabel();

            if ($selectedChildren > 0) {
                $label = sprintf('<strong>%s</strong>', $label);
            }

            $result[] = array(
                'attr' => array(
                    'id' => sprintf('node_%d', $category['item']->getId())
                ),
                'data'  => $label,
                'state' => static::getCategoryState($category, $selectedIds),
                'children' => $children,
                'selectedChildrenCount' => $selectedChildren
            );
        }

        return $result;
    }

    /**
     * Get the jstree state of the category
     *
     * @param array $category
     * @param array $selectedIds
     *
     * @return string
     * @static
     */
    protected static function getCategoryState(array $category, $selectedIds = null)
    {
        $children = $category['__children'];
        $category = $category['item'];

        if (count($children) > 0) {
            $state = 'open';
        } elseif ($category->hasChildren()) {
            $state = 'closed';
        } else {
            $state = 'leaf';
        }

        if (in_array($category->getId(), $selectedIds)) {
            $state .= ' jstree-checked';
        }

        if ($category->isRoot()) {
            $state .= ' jstree-root';
        }

        return $state;
    }
}
