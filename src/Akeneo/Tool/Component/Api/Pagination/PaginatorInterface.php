<?php

namespace Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;

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
     * @param array    $items      normalized items of the collection to render
     * @param array    $parameters parameters to generate the different urls, such as uri parameters and query parameters
     * @param int|null $count      total number of items without pagination, null if not calculated
     *
     * @throws PaginationParametersException if a parameter is either invalid, undefined parameter or missing
     *
     * @return array
     */
    public function paginate(array $items, array $parameters, $count);
}
