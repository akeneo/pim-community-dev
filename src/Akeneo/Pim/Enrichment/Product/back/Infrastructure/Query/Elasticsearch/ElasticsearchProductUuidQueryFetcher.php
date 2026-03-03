<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductResults;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductUuidQueryFetcher;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ElasticsearchProductUuidQueryFetcher implements ProductUuidQueryFetcher
{
    /** @var array<string, mixed>|null */
    protected ?array $esQuery = null;
    protected int $count;
    /** @var mixed[] */
    private array $searchAfter = [];

    public function __construct(private Client $esClient, private int $pageSize)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $esQuery): void
    {
        if (!\in_array('id', $esQuery['_source'] ?? [])) {
            $esQuery['_source'][] = 'id';
        }
        if (!\in_array('document_type', $esQuery['_source'] ?? [])) {
            $esQuery['_source'][] = 'document_type';
        }

        $this->esQuery = $esQuery;
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->searchAfter = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNextResults(): ProductResults
    {
        Assert::notNull($this->esQuery, 'The query is not instantiated');

        $esQuery = $this->esQuery;
        $esQuery['size'] = $this->pageSize;

        if (0 === $esQuery['size']) {
            return new ProductResults([], 0);
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
        $count = $response['hits']['total']['value'];

        $uuids = [];
        foreach ($response['hits']['hits'] as $hit) {
            Assert::same($hit['_source']['document_type'], ProductInterface::class);
            $uuids[] = Uuid::fromString(\str_replace('product_', '', $hit['_source']['id']));
        }

        $lastResult = \end($response['hits']['hits']);
        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return new ProductResults($uuids, $count);
    }
}
