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
     * @param                 $pageSize
     */
    public function __construct(CursorInterface $cursor, $pageSize)
    {
        $this->cursor = $cursor;
        $this->pageSize = $pageSize;
        $this->pageNumber = 0;
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
     *
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return int
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
        $this->pageNumber++;
        $this->pageData = $this->getNextDataPage();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->pageNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->pageNumber * $this->pageSize < $this->cursor->count();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->cursor->rewind();
        $this->pageNumber = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        round($this->cursor->count() / $this->pageSize);
    }

    /**
     * @return array
     */
    private function getNextDataPage()
    {
        $result = [];

        for ($i = 0; $i < $this->pageSize; $i++) {
            $this->cursor->next();
            $current = $this->cursor->current();
            if ($current != null) {
                $result[] = $current;
            } else {
                break;
            }
        }

        return $result;
    }
}
