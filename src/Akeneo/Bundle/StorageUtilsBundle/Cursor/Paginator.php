<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Cursor;

/**
 * Class Paginator that paginate over cursors
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Paginator implements PaginatorInterface
{
    /** @type CursorInterface */
    protected $cursor;

    /** @type int */
    protected $pageSize;

    /** @type int */
    protected $currentPage;

    /**
     * @param CursorInterface $cursor
     * @param $pageSize
     */
    public function __construct(CursorInterface $cursor, $pageSize)
    {
        $this->cursor = $cursor;
        $this->pageSize = $pageSize;
        $this->currentPage = 0;
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->currentPage*$this->pageSize<$this->cursor->count();
    }

    /**
     * @return array
     */
    public function getNextPage()
    {
        if ($this->hasNextPage()) {
            $result = [];

            for ($i = 0; $i<$this->pageSize; $i++) {
                $result[] = $this->cursor->getNext();
            }

            $this->currentPage++;
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->cursor->rewind();
        $this->currentPage = 0;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
}
