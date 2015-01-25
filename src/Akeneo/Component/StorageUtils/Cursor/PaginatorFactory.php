<?php

namespace Akeneo\Component\StorageUtils\Cursor;

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
    protected $pageSize;

    /**
     * @param string $paginatorClass class name implementation
     * @param int    $pageSize
     */
    public function __construct($paginatorClass, $pageSize)
    {
        $this->paginatorClass = $paginatorClass;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginator(CursorInterface $cursor, $pageSize = null)
    {
        if (null === $pageSize) {
            $pageSize = $this->pageSize;
        }

        return new $this->paginatorClass($cursor, $pageSize);
    }
}
