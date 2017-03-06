<?php

namespace Pim\Component\Api\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Repository interface for product resources
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepositoryInterface extends IdentifiableObjectRepositoryInterface
{
    /**
     * Find products with offset > $offset and filtered by $criteria
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param int                          $limit
     * @param int                          $offset
     *
     * @return array
     */
    public function searchAfterOffset(ProductQueryBuilderInterface $pqb, $limit, $offset);

    /**
     * Return the count of products filtered by PQB
     *
     * @param ProductQueryBuilderInterface $pqb
     *
     * @return int
     */
    public function count(ProductQueryBuilderInterface $pqb);
}
