<?php
namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

/**
 * Interface PaginatorFactoryInterface to instantiate paginator to iterate page of entities
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PaginatorFactoryInterface
{
    /**
     * Create the paginator with the correct implementation and parameters from context
     *
     * @param  CursorInterface    $cursor
     * @return PaginatorInterface
     */
    public function createPaginator(CursorInterface $cursor);
}
