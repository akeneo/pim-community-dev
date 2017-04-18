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
    /** @var array */
    protected $searchAfterIdentifier;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $fetchedItemsCount;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $repository
     * @param array                         $esQuery
     * @param string                        $indexType
     * @param int                           $pageSize
     * @param int                           $limit
     * @param string|null                   $searchAfterIdentifier
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        $indexType,
        $pageSize,
        $limit,
        $searchAfterIdentifier = null
    ) {
        $this->limit = $limit;

        if (null !== $searchAfterIdentifier) {
            $this->searchAfter = [$indexType . '#' . $searchAfterIdentifier];
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
