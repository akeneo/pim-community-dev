<?php

namespace Oro\Bundle\ImapBundle\Connector\Search;

use Oro\Bundle\ImapBundle\Connector\Search\SearchQueryExprInterface;

/**
 * Represents the search query expression
 */
class SearchQueryExpr implements SearchQueryExprInterface, \Iterator, \ArrayAccess
{
    /**
     * @var SearchQueryExprInterface[]
     */
    private $items;

    /** @var int The current position of the iterator */
    private $position = 0;

    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * @param SearchQueryExprInterface $item
     */
    public function add(SearchQueryExprInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param SearchQueryExprInterface[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return SearchQueryExprInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Checks if this object has no any expressions.
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (empty($this->items)) {
            return true;
        }
        $isEmpty = true;
        foreach ($this->items as $item) {
            if ($item instanceof SearchQueryExprValueBase) {
                $value = $item->getValue();
                $isEmpty = ($value instanceof SearchQueryExpr)
                    ? $value->isEmpty()
                    : false;
            } elseif ($item instanceof SearchQueryExpr) {
                $isEmpty = $item->isEmpty();
            } else {
                $isEmpty = false;
            }
            if (!$isEmpty) {
                break;
            }
        }

        return $isEmpty;
    }

    /**
     * Checks if this object has more than one expression.
     *
     * @return bool
     */
    public function isComplex()
    {
        if (empty($this->items)) {
            return false;
        }
        if (count($this->items) > 1) {
            return true;
        }
        $isComplex = false;
        $item = $this->items[0];
        if ($item instanceof SearchQueryExprValueBase) {
            $value = $item->getValue();
            if ($value instanceof SearchQueryExpr) {
                $isComplex = $value->isComplex();
            }
        } elseif ($item instanceof SearchQueryExpr) {
            $isComplex = $item->isComplex();
        }

        return $isComplex;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
