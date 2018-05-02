<?php

namespace Akeneo\Tool\Component\StorageUtils\Cursor;

/**
 * Class PaginatorFactory to instantiate paginator to iterate page of entities
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PaginatorFactory implements PaginatorFactoryInterface
{
    /** @var string */
    protected $paginatorClass;

    /** @var int */
    protected $defaultPageSize;

    /**
     * @param string $paginatorClass  class name implementation
     * @param int    $defaultPageSize the default page size
     */
    public function __construct($paginatorClass, $defaultPageSize)
    {
        $this->paginatorClass = $paginatorClass;
        $this->defaultPageSize = $defaultPageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginator(CursorInterface $cursor, $pageSize = null)
    {
        if (null === $pageSize) {
            $pageSize = $this->defaultPageSize;
        }

        return new $this->paginatorClass($cursor, $pageSize);
    }
}
