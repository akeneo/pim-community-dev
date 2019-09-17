<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Common logic shared by all our cursors.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var CursorableRepositoryInterface */
    protected $repository;

    /** @var array */
    protected $esQuery;

    /** @var array */
    protected $items;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $count;

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        if (empty($this->items)) {
            return null;
        }

        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return $this->count;
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * @param array $esQuery
     *
     * @return array
     */
    protected function getNextItems(array $esQuery)
    {
        $identifiers = $this->getNextIdentifiers($esQuery);
        if (empty($identifiers)) {
            return [];
        }

        $hydratedItems = $this->repository->getItemsFromIdentifiers($identifiers);
        $orderedItems = [];
        foreach ($identifiers as $identifier) {
            foreach ($hydratedItems as $hydratedItem) {
                // sometimes $identifier is only numerical whereas getIdentifier() / getCode() returns a string
                if (
                    method_exists($hydratedItem, 'getIdentifier')
                    && (string) $identifier === $hydratedItem->getIdentifier()
                ) {
                    $orderedItems[] = $hydratedItem;
                    break;
                } elseif (
                    method_exists($hydratedItem, 'getCode')
                    && (string) $identifier === $hydratedItem->getCode()
                ) {
                    $orderedItems[] = $hydratedItem;
                    break;
                }
            }
        }

        return $orderedItems;
    }

    /**
     * Get the next identifiers from the Elasticsearch query
     *
     * @param array $esQuery
     *
     * @return array
     */
    abstract protected function getNextIdentifiers(array $esQuery);
}
