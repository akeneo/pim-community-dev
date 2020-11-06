<?php

namespace Akeneo\Tool\Component\StorageUtils\Cursor;

/**
 * interface PaginatorInterface that paginate over cursors
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PaginatorInterface extends \Countable, \Iterator
{
    public function getPageSize(): int;

    /**
     * @param $pageSize
     *
     * @return $this
     */
    public function setPageSize($pageSize): self;

    public function getPageNumber(): int;
}
