<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

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
    private $paginatorClass = null;

    /** @type int */
    private $pageSize;

    /**
     * @param string $paginatorClass class name implementation
     * @param int    $pageSize
     */
    public function __construct(
        $paginatorClass,
        $pageSize
    ) {
        $this->paginatorClass = $paginatorClass;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginator(CursorInterface $cursor)
    {
        return new $this->paginatorClass($cursor, $this->pageSize);
    }
}
