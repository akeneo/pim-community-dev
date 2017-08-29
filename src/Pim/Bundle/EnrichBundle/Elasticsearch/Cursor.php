<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Cursor to iterate over all items.
 * Internally, this is implemented with the search after pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html}
 *
 * This cursor is dedicated to the search in the datagrid where we need to have 2 types of objects:
 * products and product models.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Cursor extends AbstractCursor implements CursorInterface
{
    /** @var array */
    private $esQuery;

    /** @var string */
    private $indexType;

    /** @var int */
    private $pageSize;

    /** @var array */
    private $searchAfter;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $productRepository
     * @param CursorableRepositoryInterface $productModelRepository
     * @param array                         $esQuery
     * @param string                        $indexType
     * @param int                           $pageSize
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        array $esQuery,
        string $indexType,
        int $pageSize
    ) {
        $this->esClient = $esClient;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->esQuery = $esQuery;
        $this->indexType = $indexType;
        $this->pageSize = $pageSize;
        $this->searchAfter = [];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->searchAfter = [];
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery)
    {
        $esQuery['size'] = $this->pageSize;

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[$hit['_source']['identifier']] = $hit['_source']['product_type'];
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }
}
