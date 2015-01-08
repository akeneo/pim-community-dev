<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

/**
 * Class Paginator that paginate over cursors
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PaginatorInterface
{
    /**
     * @return int
     */
    public function getPageSize();

    /**
     * @return int
     */
    public function getCurrentPage();

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize);

    /**
     * @return bool
     */
    public function hasNextPage();

    /**
     * @return array
     */
    public function getNextPage();

    /**
     * @return mixed
     */
    public function rewind();
}
