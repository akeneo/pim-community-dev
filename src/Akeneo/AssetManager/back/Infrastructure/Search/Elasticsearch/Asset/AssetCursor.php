<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCursor implements CursorInterface
{
    private int $count;
    private ?array $items = null;
    private ?string $searchAfterCode = null;

    public function __construct(
        private AssetQueryBuilderInterface $queryBuilder,
        private Client $assetClient,
        private AssetQuery $assetQuery
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
            $this->items = $this->getNextItems($this->assetQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->items = $this->getNextItems($this->assetQuery);
        reset($this->items);
    }

    private function getNextItems(AssetQuery $assetQuery): array
    {
        $elasticSearchQuery = $this->queryBuilder->buildFromQuery($assetQuery, 'code');

        $matches = $this->assetClient->search($elasticSearchQuery);

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
        return array_map(fn (array $hit) => $hit['_source']['code'], $matches['hits']['hits']);
    }

    private function nextPage()
    {
        $searchAfterCode = AssetCode::fromString($this->searchAfterCode);
        $this->assetQuery = AssetQuery::createNextWithSearchAfter($this->assetQuery, $searchAfterCode);
    }
}
