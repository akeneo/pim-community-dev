<?php

namespace Akeneo\Tool\Component\Api\Repository;

/**
 * Pageable repository interface to paginate resources
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PageableRepositoryInterface
{
    /**
     * Find resources with offset > $offset and filtered by $criteria
     *
     * @param array $criteria
     * @param array $orders
     * @param int   $limit
     * @param int   $offset
     */
    public function searchAfterOffset(array $criteria, array $orders, int $limit, int $offset): array;

    /**
     * Return the count of resources filtered by $criteria
     *
     * @param array $criteria
     */
    public function count(array $criteria = []): int;
}
