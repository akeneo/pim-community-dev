<?php

namespace Akeneo\Component\Classification\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Item category repository interface
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ItemCategoryRepositoryInterface
{
    /**
     * Return the number of times the item is present in each tree
     *
     * @param mixed $item The item to look for in the trees
     *
     * @throws \InvalidArgumentException If the $item belongs to a class we don't handle
     * @return array Each row of the array has the format:'tree'=>treeObject, 'itemCount'=>integer
     *
     */
    public function getItemCountByTree($item);

    /**
     * Get item ids linked to a category or its children.
     * You can define if you just want to get the property of the actual node or with its children with the direct
     * parameter
     *
     * @param CategoryInterface $category   the requested node
     * @param QueryBuilder      $categoryQb category query buider
     *
     * @return array
     */
    public function getItemIdsInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null);

    /**
     * Count items linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param CategoryInterface $category   the requested category node
     * @param QueryBuilder      $categoryQb category query buider
     *
     * @return int
     */
    public function getItemsCountInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null);
}
