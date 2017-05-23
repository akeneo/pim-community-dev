<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BoundedFromCursor extends Cursor implements CursorInterface
{
    /** @var int */
    protected $from;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $to;

    /** @var int */
    protected $fetchedItemsCount;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $repository
     * @param array                         $esQuery
     * @param string                        $indexType
     * @param int                           $pageSize
     * @param int                           $limit
     * @param int                           $from
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        $indexType,
        $pageSize,
        $limit,
        $from = 0
    ) {
        $this->limit = $limit;
        $this->from = $from;
        $this->to = $this->from + $this->limit;

        parent::__construct($esClient, $repository, $esQuery, $indexType, $pageSize);
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemsCountToFetch()
    {
        return ($this->to - $this->from) > $this->pageSize ? $this->pageSize : ($this->to - $this->from);
    }

    protected function getNextIdentifiers(array $esQuery)
    {
        $size = $this->getItemsCountToFetch();
        $esQuery['size'] = $size;

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['from'] = $this->from;

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        $this->from += $size;

        return $identifiers;
    }
}
