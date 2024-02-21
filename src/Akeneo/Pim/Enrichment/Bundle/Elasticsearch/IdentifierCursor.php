<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\ResultAwareInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Cursor to iterate over all identifiers. Internally it uses search_after to return all identifiers.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierCursor implements CursorInterface, ResultAwareInterface
{
    protected ?array $items = null;
    protected int $count;
    private array $searchAfter = [];
    private ?ResultInterface $result;

    public function __construct(private Client $esClient, private array $esQuery, private int $pageSize)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function current(): mixed
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return \current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key(): mixed
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return \key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        if (false === next($this->items)) {
            $this->items = $this->getNextIdentifiers($this->esQuery)->all();
            \reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->searchAfter = [];
        $this->items = $this->getNextIdentifiers($this->esQuery)->all();
        \reset($this->items);
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
            $sort = \array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['track_total_hits'] = true;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($esQuery);
        $this->result = new ElasticsearchResult($response);
        $this->count = $response['hits']['total']['value'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type'], $hit['_source']['id']);
        }

        $lastResult = \end($response['hits']['hits']);
        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(): ResultInterface
    {
        if (null === $this->result) {
            $this->getNextIdentifiers($this->esQuery);
        }

        return $this->result;
    }
}
