<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Product category repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductCategoryRepositoryInterface extends
    IdentifiableObjectRepositoryInterface,
    ItemCategoryRepositoryInterface,
    CategoryFilterableRepositoryInterface
{
    /**
     * Apply a filter by product ids
     *
     * @param mixed $qb         query builder to update
     * @param array $productIds product ids
     * @param bool  $include    true for in, false for not in
     */
    public function applyFilterByIds($qb, array $productIds, $include);
}
