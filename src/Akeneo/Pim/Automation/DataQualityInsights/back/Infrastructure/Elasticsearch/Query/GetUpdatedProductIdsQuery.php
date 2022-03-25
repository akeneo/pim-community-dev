<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpdatedProductIdsQuery implements GetUpdatedProductIdsQueryInterface
{
    public function __construct(private Client $esClient, private Connection $connection)
    {
    }

    public function since(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator
    {
        $query = [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'document_type' => ProductInterface::class
                        ],
                    ],
                    [
                        'range' => [
                            'updated' => [
                                'gt' => $updatedSince->setTimezone(new \DateTimeZone('UTC'))->format('c')
                            ],
                        ]
                    ],
                ],
            ],
        ];

        $totalProducts = $this->countUpdatedProducts($query);

        $searchQuery = [
            '_source' => ['id'],
            'size' => $bulkSize,
            'sort' => ['_id' => 'asc'],
            'query' => $query
        ];

        $result = $this->esClient->search($searchQuery);
        $searchAfter = [];
        $returnedProducts = 0;

        while (!empty($result['hits']['hits'])) {
            $productUuids = [];
            $previousSearchAfter = $searchAfter;
            foreach ($result['hits']['hits'] as $product) {
                $productUuids[] = $this->extractUuid($product);
                $searchAfter = $product['sort'] ?? $searchAfter;
            }

            yield $this->findProductIdFromUuid($productUuids);

            $returnedProducts += count($productUuids);
            $result = $returnedProducts < $totalProducts && $searchAfter !== $previousSearchAfter
                ? $this->searchAfter($searchQuery, $searchAfter)
                : [];
        }
    }

    private function searchAfter(array $query, array $searchAfter): array
    {
        if (!empty($searchAfter)) {
            $query['search_after'] = $searchAfter;
        }

        return $this->esClient->search($query);
    }

    private function formatProductId(array $productData): ProductId
    {
        if (!isset($productData['_source']['id'])) {
            throw new \Exception('No id not found in source when searching updated products');
        }

        $productId =  intval(str_replace('product_', '', $productData['_source']['id']));

        return new ProductId($productId);
    }

    private function extractUuid(array $productData): UuidInterface
    {
        if (!isset($productData['_source']['id'])) {
            throw new \Exception('No id not found in source when searching updated products');
        }

        return Uuid::fromString(str_replace('product_', '', $productData['_source']['id']));
    }

    /**
     * @todo: remove this class when ProductId can accept a uuid
     *
     * @param UuidInterface[] $productUuids
     * @return ProductId[]
     */
    private function findProductIdFromUuid(array $productUuids): array
    {
        if ([] === $productUuids) {
            return [];
        }

        $sql = 'SELECT id FROM pim_catalog_product WHERE uuid IN (:product_uuids)';

        $ids = $this->connection->executeQuery(
            $sql,
            ['product_uuids' => \array_map(fn (UuidInterface $uuid): string => $uuid->getBytes(), $productUuids)],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return \array_map(fn ($id): ProductId => new ProductId((int) $id), $ids);
    }

    private function countUpdatedProducts(array $query): int
    {
        $count = $this->esClient->count([
            'query' => $query,
        ]);

        return $count['count'] ?? 0;
    }
}
