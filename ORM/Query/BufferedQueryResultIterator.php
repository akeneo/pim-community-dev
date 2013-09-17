<?php

namespace Oro\Bundle\BatchBundle\ORM\Query;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

/**
 * Iterates results of Query using buffer, allows to iterate large query
 * results without risk of getting out of memory error
 */
class BufferedQueryResultIterator implements \Iterator, \Countable
{
    /**
     * Count of records that will be loaded on each page during iterations
     */
    const DEFAULT_BUFFER_SIZE = 200;

    /**
     * Count of records that will be loaded on each page during iterations
     *
     * @var int
     */
    protected $bufferSize = self::DEFAULT_BUFFER_SIZE;

    /**
     * The parameters map of this query
     *
     * @var array
     */
    protected $parameters;

    /**
     * Defines the processing mode to be used during hydration / result set transformation
     *
     * @var integer
     */
    protected $hydrationMode = null;

    /**
     * Query to iterate
     *
     * @var Query
     */
    protected $query;

    /**
     * Total count of records in query
     *
     * @var int
     */
    protected $totalCount;

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
    protected $rewound = false;

    /**
     * Current ResultRecord, populated from query result row
     *
     * @var mixed
     */
    protected $current = null;

    /**
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = clone $query;
    }

    /**
     * @param Query $query
     * @return BufferedQueryResultIterator
     */
    public static function create(Query $query)
    {
        return new static($query);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return BufferedQueryResultIterator
     */
    public static function createFromQueryBuilder(QueryBuilder $queryBuilder)
    {
        return new static($queryBuilder->getQuery());
    }

    /**
     * Sets size of buffer that is queried from storage to iterate results
     *
     * @param int $bufferSize
     * @return BufferedQueryResultIterator
     * @throws \InvalidArgumentException If page size is not greater than 0
     */
    public function setBufferSize($bufferSize)
    {
        $this->assertQueryWasNotExecuted('buffer size');
        $this->bufferSize = (int) $bufferSize;
        if ($this->bufferSize <= 0) {
            throw new \InvalidArgumentException('$bufferSize must be greater than 0');
        }
        if ($this->query->getMaxResults() && $this->query->getMaxResults() < $this->bufferSize) {
            $this->bufferSize = $this->query->getMaxResults();
        }
        return $this;
    }

    /**
     * @param ArrayCollection|array $parameters
     * @return BufferedQueryResultIterator
     * @throws \InvalidArgumentException
     */
    public function setParameters($parameters)
    {
        $this->assertQueryWasNotExecuted('parameters');
        if ($parameters instanceof ArrayCollection) {
            $this->parameters = $parameters->getValues();
        } elseif (is_array($parameters)) {
            $this->parameters = $parameters;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    '$parameters is expect to be an array or instance of "%s", "%s" is given',
                    'Doctrine\Common\Collections\ArrayCollection',
                    is_object($parameters) ? get_class($parameters) : gettype($parameters)
                )
            );
        }
        return $this;
    }

    /**
     * @param integer $hydrationMode Processing mode to be used during the hydration process.
     * @return BufferedQueryResultIterator
     */
    public function setHydrationMode($hydrationMode)
    {
        $this->assertQueryWasNotExecuted('hydration mode');
        $this->hydrationMode = $hydrationMode;
        return $this;
    }

    /**
     * Asserts that query was not executed, otherwise raise an exception
     *
     * @param string $optionLabel
     * @throws \LogicException
     */
    protected function assertQueryWasNotExecuted($optionLabel)
    {
        if (null !== $this->rows) {
            throw new \LogicException(sprintf('Cannot set %s object as query was already executed.', $optionLabel));
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

        $this->current = $this->rows[$this->offset];
        $this->key = $this->offset + $this->bufferSize * $this->page;

        return $this->current;
    }

    /**
     * Attempts to load next page
     *
     * @return bool If page loaded successfully
     */
    protected function loadNextPage()
    {
        $totalPages = ceil($this->count() / $this->bufferSize);
        if (!$totalPages || $totalPages <= $this->page + 1) {
            unset($this->rows);

            return false;
        }

        $this->page++;
        $this->offset = 0;

        $pageQuery = clone $this->query;
        $pageQuery->setFirstResult($this->bufferSize * $this->page + $this->query->getFirstResult());
        $pageQuery->setMaxResults($this->bufferSize);

        $this->rows = $pageQuery->execute($this->parameters, $this->hydrationMode);

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
            $countQuery = clone $this->query;
            foreach ($this->query->getHints() as $name => $value) {
                $countQuery->setHint($name, $value);
            }
            $this->totalCount = QueryCountCalculator::calculateCount($countQuery, $this->parameters);
        }

        return $this->totalCount;
    }
}
