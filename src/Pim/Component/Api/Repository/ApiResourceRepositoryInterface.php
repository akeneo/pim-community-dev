<?php

namespace Pim\Component\Api\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * This interface should be used for simple entities on the API,
 * when the only need is to paginate and require a single resource.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ApiResourceRepositoryInterface extends IdentifiableObjectRepositoryInterface
{
    /**
     * Find resources with offset >= $offset and filtered by $criteria
     *
     * @param array $criteria
     * @param array $orders
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function searchAfterOffset(array $criteria, array $orders, $limit, $offset);

    /**
     * Return the count of products filtered by $criteria
     *
     * @param array $criteria
     *
     * @return int
     */
    public function count(array $criteria);
}
