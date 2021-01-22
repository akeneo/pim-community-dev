<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCursor implements CursorInterface
{
    private AssetQueryBuilderInterface $queryBuilder;
    private Client $assetClient;
    private AssetQuery $assetQuery;
    private int $pageSize;

    private int $count;
    private array $items;
    private array $searchAfter;

    public function __construct(
        AssetQueryBuilderInterface $queryBuilder,
        Client $assetClient,
        AssetQuery $assetQuery,
        int $pageSize
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->assetClient = $assetClient;
        $this->assetQuery = $assetQuery;
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
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
    public function rewind()
    {
        $this->searchAfter = [];
        $this->items = $this->getNextItems($this->assetQuery);
        reset($this->items);
    }

    /**
     * Get the next asset codes.
     *
     * @param array $esQuery
     *
     * @return array
     */
    private function getNextItems(AssetQuery $assetQuery): array
    {
        if (!empty($this->items)) {
            return [];
        }

        $elasticSearchQuery = $this->queryBuilder->buildFromQuery($assetQuery, 'code');
        $matches = $this->assetClient->search($elasticSearchQuery);

        $this->count = $matches['hits']['total']['value'];

        return $this->getCodes($matches);
    }

    private function getCodes(array $matches): array
    {
        $codes = array_map(function (array $hit) {
            return $hit['code'];
        }, $matches['hits']['hits']);

        return $codes;
    }

    private function nextPage()
    {
        // TODO
    }
}
