<?php

namespace Akeneo\Tool\Component\Classification\Repository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryFilterableRepositoryInterface
{
    const JOIN_ALIAS = 'CategoryFilterableRepositoryInterface';

    /**
     * Apply a filter by unclassified (not placed in any categories)
     *
     * @param mixed $qb query builder to update
     */
    public function applyFilterByUnclassified($qb);

    /**
     * Apply a filter by category ids
     *
     * @param mixed $qb          query builder to update
     * @param array $categoryIds category ids
     * @param bool  $include     if yes, get item in those categories, if false
     *                           items NOT in those categories
     */
    public function applyFilterByCategoryIds($qb, array $categoryIds, $include = true);

    /**
     * Apply filter by category ids or unclassified
     *
     * @param mixed $qb          query builder to update
     * @param array $categoryIds category ids
     */
    public function applyFilterByCategoryIdsOrUnclassified($qb, array $categoryIds);
}
