<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Bounded cursor to iterate over items where a start and a limit are defined.
 * Internally, this is implemented with the from/size pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
 *
 * This cursor is dedicated to the search in the datagrid where we need to have 2 types of objects:
 * products and product models.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeCursor extends AbstractCursor implements CursorInterface
{
    /** @var array */
    protected $esQuery;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $initialFrom;

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
     * @param CursorableRepositoryInterface $productRepository
     * @param CursorableRepositoryInterface $productModelRepository
     * @param array                         $esQuery
     * @param int                           $pageSize
     * @param int                           $limit
     * @param int                           $from
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        array $esQuery,
        $pageSize,
        $limit,
        $from = 0
    ) {
        $this->esClient = $esClient;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->esQuery = $esQuery;
        $this->pageSize = $pageSize;
        $this->limit = $limit;
        $this->from = $from;
        $this->initialFrom = $from;
        $this->to = $this->from + $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->from += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->from = $this->initialFrom;
        $this->to = $this->from + $this->limit;
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }

    /**
     * {@inheritdoc}
     *
     * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
     */
    protected function getNextIdentifiers(array $esQuery): IdentifierResults
    {
        $size = ($this->to - $this->from) > $this->pageSize ? $this->pageSize : ($this->to - $this->from);
        $esQuery['size'] = $size;
        $identifiers = new IdentifierResults();

        if (0 === $esQuery['size']) {
            return $identifiers;
        }

        $sort = ['_id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['from'] = $this->from;
        $esQuery['track_total_hits'] = true;

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        return $identifiers;
    }
}
