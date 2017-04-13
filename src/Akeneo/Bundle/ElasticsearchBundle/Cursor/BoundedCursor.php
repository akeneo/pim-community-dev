<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Bounded cursor to iterate on items where a start and a limit are defined
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BoundedCursor extends Cursor implements CursorInterface
{
    /** @var string|null */
    protected $searchAfterUniqueKey;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $fetchedItemsCount;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $repository
     * @param array                         $esQuery
     * @param array                         $searchAfter
     * @param string                        $indexType
     * @param int                           $pageSize
     * @param int                           $limit
     * @param string|null                   $searchAfterUniqueKey
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        array $searchAfter = [],
        $indexType,
        $pageSize,
        $limit,
        $searchAfterUniqueKey = null
    ) {
        $this->limit = $limit;
        $this->searchAfter = $searchAfter;
        $this->searchAfterUniqueKey = $searchAfterUniqueKey;

        if (null !== $searchAfterUniqueKey) {
            array_push($this->searchAfter, $indexType . '#' . $searchAfterUniqueKey);
        }

        parent::__construct($esClient, $repository, $esQuery, $indexType, $pageSize);
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemsCountToFetch()
    {
        $itemsCountToFetch = $this->limit > $this->pageSize ? $this->pageSize : $this->limit;
        if (null !== $this->fetchedItemsCount && ($this->fetchedItemsCount + $itemsCountToFetch) > $this->limit) {
            $itemsCountToFetch = $this->fetchedItemsCount - $this->limit;
        }
        $this->fetchedItemsCount += $itemsCountToFetch;

        return $itemsCountToFetch;
    }
}
