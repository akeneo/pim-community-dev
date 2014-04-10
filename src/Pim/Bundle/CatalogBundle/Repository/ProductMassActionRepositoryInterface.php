<?php

namespace Pim\Bundle\CatalogBundle\Repository;

/**
 * Product mass action repository interface
 * Methods have been extracted from ProductRepository to be specialized for mass actions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductMassActionRepositoryInterface
{
    /**
     * Apply a filter by product ids
     *
     * @param mixed   $qb         query builder to update
     * @param array   $productIds product ids
     * @param boolean $include    true for in, false for not in
     */
    public function applyFilterByIds($qb, array $productIds, $include);

    /**
     * Find all common attribute ids linked to a family or with values from a list of product ids
     *
     * @param array $productIds
     *
     * @return integer[]
     */
    public function findCommonAttributeIds(array $productIds);
}
