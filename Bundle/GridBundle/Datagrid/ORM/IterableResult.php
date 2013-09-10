<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;

/**
 * Iterates ProxyQuery with elements of ResultRecord type
 */
class IterableResult implements IterableResultInterface, \Countable
{
    /**
     * Default page size, count of records that will be loaded on each page during iterations
     */
    const DEFAULT_PAGE_SIZE = 200;

    /**
     * Query to iterate
     *
     * @var ProxyQuery
     */
    protected $query;

    /**
     * Total count of records in query
     *
     * @var int
     */
    protected $totalCount;

    /**
     * Count of records that will be loaded on each page during iterations
     *
     * @var int
     */
    protected $pageSize;

    /**
     * Index of page, max page is calculated using $totalCount and $pageSize
     *
     * @var int
     */
    protected $page = -1;

    /**
     * Offset of current record in current page
     *
     * @var int
     */
    protected $offset = -1;

    /**
     * Global key of record
     *
     * @var int
     */
    protected $key = -1;

    /**
     * Rows that where loaded for current page
     *
     * @var array
     */
    protected $rows;

    /**
     * Is iterator was rewound, only one rewind is allowed
     *
     * @var boolean
     */
    private $rewound = false;

    /**
     * Current ResultRecord, populated from query result row
     *
     * @var ResultRecord|null
     */
    private $current = null;

    /**
     * @param  ProxyQuery                $query
     * @param  int                       $pageSize
     * @throws \InvalidArgumentException If page size is not greater than 0
     */
    public function __construct(
        ProxyQuery $query,
        $pageSize = self::DEFAULT_PAGE_SIZE
    ) {
        $this->query = clone $query;
        $this->setBufferSize($pageSize);
    }

    /**
     * {@inheritDoc}
     */
    public function setBufferSize($pageSize)
    {
        $this->pageSize = (int) $pageSize;
        if ($this->pageSize <= 0) {
            throw new \InvalidArgumentException('$pageSize must be greater than 0');
        }
        if ($this->query->getMaxResults() && $this->query->getMaxResults() < $this->pageSize) {
            $this->pageSize = $this->query->getMaxResults();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->offset++;

        if (!isset($this->rows[$this->offset]) && !$this->loadNextPage()) {
            return $this->current = null;
        }

        $this->current = new ResultRecord($this->rows[$this->offset]);
        $this->key = $this->offset + $this->pageSize * $this->page;

        return new ResultRecord($this->rows[$this->offset]);
    }

    /**
     * Attempts to load next page
     *
     * @return bool If page loaded successfully
     */
    protected function loadNextPage()
    {
        $totalPages = ceil($this->count() / $this->pageSize);
        if (!$totalPages || $totalPages <= $this->page + 1) {
            unset($this->rows);

            return false;
        }

        $this->page++;
        $this->offset = 0;

        $pageQuery = clone $this->query;
        $pageQuery->setFirstResult($this->pageSize * $this->page + $this->query->getFirstResult());
        $pageQuery->setMaxResults($this->pageSize);

        $this->rows = $pageQuery->execute();

        if (!count($this->rows)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return $this->current !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        if ($this->rewound == true) {
            throw new \RuntimeException("Can only iterate a Result once.");
        } else {
            $this->current = $this->next();
            $this->rewound = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        if (null === $this->totalCount) {
            $countCalculator = new CountCalculator();
            $countQuery = $this->query->getQueryBuilder()->getQuery();
            foreach ($this->query->getQueryHints() as $name => $value) {
                $countQuery->setHint($name, $value);
            }
            $this->totalCount = $countCalculator->getCount($countQuery);
        }

        return $this->totalCount;
    }
}
