<?php

namespace Akeneo\Component\StorageUtils\Cursor;

/**
 * Interface CursorFactoryInterface
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CursorFactoryInterface
{
    /**
     * Create the cursor with the correct implementation
     *
     * @param mixed    $queryBuilder Query builder
     * @param int|null $pageSize     Optional page size for reading data
     *
     * @return CursorInterface
     */
    public function createCursor($queryBuilder, $pageSize = null);
}
