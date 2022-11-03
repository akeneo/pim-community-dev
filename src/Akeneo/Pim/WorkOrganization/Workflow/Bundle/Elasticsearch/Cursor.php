<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class Cursor extends AbstractCursor implements CursorInterface
{
    /** @var array */
    private $esQuery;

    /** @var int */
    private $pageSize;

    /** @var array */
    private $searchAfter;

    /**
     * @param Client                        $esClient
     * @param CursorableRepositoryInterface $productDraftRepository
     * @param CursorableRepositoryInterface $productModelDraftRepository
     * @param array                         $esQuery
     * @param int                           $pageSize
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $productDraftRepository,
        CursorableRepositoryInterface $productModelDraftRepository,
        array $esQuery,
        int $pageSize
    ) {
        $this->esClient = $esClient;
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->esQuery = $esQuery;
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
    protected function getNextIdentifiers(array $esQuery): IdentifierResults
    {
        $esQuery['size'] = $this->pageSize;
        $identifiers = new IdentifierResults();

        if (0 === $esQuery['size']) {
            return $identifiers;
        }

        $sort = ['id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }
}
