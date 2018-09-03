<?php

namespace Akeneo\Tool\Component\Api\Repository;

/**
 * Pageable repository interface to paginate resources with search after method
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SearchAfterPageableRepositoryInterface
{
    /**
     * Find resources with identifier > $identifier and filtered by $criteria.
     * Identifier represents a unique property of the entity. It can be the code.
     *
     * When applying an "order by" on the query, the same columns should be specified in the from array.
     * The last column should always be the identifier column.
     *
     * Example :
     * order by: ['root' => 'ASC', 'left' => 'ASC', 'code' => 'ASC']
     * from : ['root' => 1, 'left' => 3, 'code' => 'car']
     *
     * From array can be empty when getting the first page.
     *
     * @param array $criteria criteria to filter the result set
     * @param array $orders   order of the data
     * @param int   $limit    number of elements to get
     * @param array $from     columns to start the research from.
     *                        It should have the same number of columns that in the from.
     *
     * @return array
     */
    public function searchAfterIdentifier(array $criteria, array $orders, int $limit, array $from = []);
}
