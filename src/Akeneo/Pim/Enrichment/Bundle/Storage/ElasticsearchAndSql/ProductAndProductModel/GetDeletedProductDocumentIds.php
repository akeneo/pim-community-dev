<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Fetches all product documents in the ES index that are not present in the DB anymore
 */
final class GetDeletedProductDocumentIds
{
    private const CHUNK_SIZE = 500;

    public function __construct(
        private readonly Client $client,
        private readonly GetProductUuids $getProductUuids,
    ) {
    }

    /**
     * @return iterable<string>
     */
    public function __invoke(): iterable
    {
        foreach ($this->getAllESProductDocumentIds() as $ids) {
            foreach ($this->getNonExistingProductIds($ids) as $deletedId) {
                yield $deletedId;
            }
        }
    }

    /**
     * @return iterable<array<string, string>>
     */
    private function getAllESProductDocumentIds(): iterable
    {
        $query = [
            'sort' => ['id' => 'asc'],
            'size' => self::CHUNK_SIZE,
            '_source' => ['_id'],
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'term' => [
                                    'document_type' => ProductInterface::class,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $searchAfter = null;
        do {
            $resultsPage = [];

            $params = array_merge(
                $query,
                $searchAfter ? [
                    'search_after' => $searchAfter
                ] : []
            );
            $results = $this->client->search($params);
            foreach ($results['hits']['hits'] ?? [] as $result) {
                $resultsPage[$result['_id']] = \preg_replace('/^product_/', '', $result['_id']);
                $searchAfter = $result['sort'] ?? [];
            }

            yield $resultsPage;
        } while (count($resultsPage) > 0);
    }

    /**
     * @return string[]
     */
    private function getNonExistingProductIds(array $productDocumentIds): array
    {
        if (0 === \count($productDocumentIds)) {
            return [];
        }

        $uuids = \array_filter(
            \array_values($productDocumentIds),
            static fn (string $uuid): bool => Uuid::isValid($uuid),
        );

        $existingUuids = \array_keys(
            $this->getProductUuids->fromUuids(
                \array_map(static fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids)
            )
        );

        return \array_keys(\array_diff($productDocumentIds, $existingUuids));
    }
}
