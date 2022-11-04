<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\PublishedProduct;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class FromSizeCursor extends AbstractCursor implements CursorInterface
{
    private int $initialFrom;
    private int $to;

    public function __construct(
        Client $esClient,
        PublishedProductRepositoryInterface $publishedProductRepository,
        private array $esQuery,
        private int $pageSize,
        private int $limit,
        private int $from = 0
    ) {
        $this->esClient = $esClient;
        $this->publishedProductRepository = $publishedProductRepository;
        $this->initialFrom = $from;
        $this->to = $this->from + $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
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
    public function rewind(): void
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
    protected function getNextIdentifiers(array $esQuery): array
    {
        $size = min(($this->to - $this->from), $this->pageSize);
        $esQuery['size'] = $size;
        $identifiers = [];

        if (0 === $esQuery['size']) {
            return $identifiers;
        }

        $sort = ['id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['from'] = $this->from;
        $esQuery['track_total_hits'] = true;

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        return $identifiers;
    }
}
