<?php

namespace Akeneo\Category\Infrastructure\Component\Classification\Repository;

/**
 * Item category repository interface.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ItemCategoryRepositoryInterface
{
    /**
     * Return the number of times the item is present in each tree.
     *
     * @param mixed $item The item to look for in the trees
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'itemCount'=>integer
     *
     * @throws \InvalidArgumentException If the $item belongs to a class we don't handle
     */
    public function getItemCountByTree($item);

    /**
     * Count items linked to category ids.
     *
     * @return int
     */
    public function getItemsCountInCategory(array $categoryIds = []);

    /**
     * Return categories linked to an item.
     */
    public function findCategoriesItem($item): array;
}
