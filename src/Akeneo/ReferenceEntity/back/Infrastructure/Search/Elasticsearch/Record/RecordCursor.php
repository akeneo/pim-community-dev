<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class RecordCursor implements CursorInterface
{
    private int $count;
    private ?array $items = null;
    private ?string $searchAfterCode = null;

    public function __construct(
        private RecordQueryBuilderInterface $queryBuilder,
        private Client $recordClient,
        private RecordQuery $recordQuery
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function current(): mixed
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
    public function key(): mixed
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items);
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
            $this->nextPage();
            $this->items = $this->getNextItems($this->recordQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->items = $this->getNextItems($this->recordQuery);
        reset($this->items);
    }

    private function getNextItems(RecordQuery $recordQuery): array
    {
        $elasticSearchQuery = $this->queryBuilder->buildFromQuery($recordQuery, 'code');

        $matches = $this->recordClient->search($elasticSearchQuery);

        $this->count = $matches['hits']['total']['value'];

        if (isset($matches['hits']['hits']) && (is_countable($matches['hits']['hits']) ? count($matches['hits']['hits']) : 0) > 0) {
            $hitsCount = is_countable($matches['hits']['hits']) ? count($matches['hits']['hits']) : 0;
            $lastHit = $matches['hits']['hits'][$hitsCount - 1];
            $this->searchAfterCode = $lastHit['sort'][0];
        }

        return $this->getCodes($matches);
    }

    private function getCodes(array $matches): array
    {
        return array_map(static fn (array $hit) => $hit['_source']['code'], $matches['hits']['hits']);
    }

    private function nextPage()
    {
        $searchAfterCode = RecordCode::fromString($this->searchAfterCode);
        $this->recordQuery = RecordQuery::createNextWithSearchAfter($this->recordQuery, $searchAfterCode);
    }
}
