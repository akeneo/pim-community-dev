<?php

namespace Akeneo\Tool\Component\Classification\Repository;

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
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'itemCount'=>integer
     */
    public function getItemCountByTree($item);

    /**
     * Count items linked to category ids
     *
     * @param array $categoryIds
     *
     * @return int
     */
    public function getItemsCountInCategory(array $categoryIds = []);

    /**
     * Return categories linked to an item
     *
     * @param mixed $item
     *
     * @return array
     */
    public function findCategoriesItem($item): array;
}
