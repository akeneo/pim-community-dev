<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Bounded cursor to iterate over items where a start and a limit are defined.
 * Internally, this is implemented with the from/size pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
 *
 * This cursor is dedicated to the search in the datagrid where we need to have 2 types of objects:
 * product drafts and product model drafts.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FromSizeCursor extends AbstractCursor implements CursorInterface
{
    protected int $initialFrom;
    protected int $to;
    protected int $fetchedItemsCount;

    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $productDraftRepository,
        CursorableRepositoryInterface $productModelDraftRepository,
        protected array $esQuery,
        protected int $pageSize,
        protected int $limit,
        protected int $from = 0
    ) {
        $this->esClient = $esClient;
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
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
        $size = min(($this->to - $this->from), $this->pageSize);
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

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        return $identifiers;
    }
}
