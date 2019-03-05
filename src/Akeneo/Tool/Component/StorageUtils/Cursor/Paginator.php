<?php

namespace Akeneo\Tool\Component\StorageUtils\Cursor;

/**
 * Class Paginator that paginate over cursors
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Paginator implements PaginatorInterface
{
    /** @var CursorInterface */
    protected $cursor;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $pageNumber;

    /** @var int */
    protected $currentPage;

    /** @var  array */
    protected $pageData;

    /**
     * @param CursorInterface $cursor
     * @param int             $pageSize
     */
    public function __construct(CursorInterface $cursor, $pageSize)
    {
        $this->cursor = $cursor;
        $this->pageSize = $pageSize;
        $this->pageNumber = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->pageData;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if ($this->valid()) {
            $this->pageNumber++;
            $this->pageData = $this->getNextDataPage();
        } else {
            $this->pageData = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if ($this->valid()) {
            return $this->pageNumber;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->pageNumber < $this->count();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->cursor->rewind();
        $this->pageNumber = 0;
        $this->pageData = $this->getNextDataPage();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return intval(ceil($this->cursor->count() / $this->pageSize));
    }

    /**
     * @return array
     */
    private function getNextDataPage()
    {
        $result = [];
        $pageSize = 0;
        do {
            if ($this->pageNumber > 0 || $pageSize > 0) {
                $this->cursor->next();
            }
            $current = $this->cursor->current();
            if (null !== $current && false !== $current) {
                $result[] = $current;
            }
            $pageSize++;
        } while ($pageSize < $this->pageSize && null !== $current && false !== $current);

        if (empty($result)) {
            return false;
        }

        return $result;
    }
}
