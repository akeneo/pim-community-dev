<?php

namespace Pim\Component\Api\Pagination;

/**
 * Paginator interface.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PaginatorInterface
{
    /**
     * Renders a paginate list of items with information to browse the different
     * pages, such as previous and next links.
     *
     * Items should be already normalized.
     *
     * @param array  $items          normalized items of the collection to render
     * @param array  $parameters     parameters of the pagination, such as request parameters
     * @param int    $count          total number of items without pagination
     * @param string $listRouteName  route name of the collection
     * @param string $itemRouteName  route name of the items in the collection
     * @param string $itemIdentifier identifier key of an item
     *
     * @return array
     */
    public function paginate(array $items, array $parameters, $count, $listRouteName, $itemRouteName, $itemIdentifier);
}
