<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Fetches all product model documents in the ES index that are not present in the DB anymore
 */
final class GetDeletedProductModelDocumentIds
{
    private const CHUNK_SIZE = 500;

    public function __construct(private readonly Client $esClient, private readonly Connection $connection)
    {
    }

    /**
     * @return iterable<string>
     */
    public function __invoke(): iterable
    {
        foreach ($this->getProductModelIdentifiersIndexedByDocumentIds() as $indexedIdentifiers) {
            foreach ($this->getNonExistingProductModelDocumentIds($indexedIdentifiers) as $documentId) {
                yield $documentId;
            }
        }
    }

    /**
     * @return iterable<array<string, string>>
     */
    private function getProductModelIdentifiersIndexedByDocumentIds(): iterable
    {
        $query = [
            'sort' => ['id' => 'asc'],
            'size' => self::CHUNK_SIZE,
            '_source' => ['identifier'],
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'term' => [
                                    'document_type' => ProductModelInterface::class,
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
            $results = $this->esClient->search($params);
            foreach ($results['hits']['hits'] ?? [] as $result) {
                $resultsPage[$result['_id']] = $result['_source']['identifier'];
                $searchAfter = $result['sort'] ?? [];
            }

            yield $resultsPage;
        } while (count($resultsPage) > 0);
    }

    /**
     * @return string[]
     */
    private function getNonExistingProductModelDocumentIds(array $indexedIdentifiers): array
    {
        if (0 === \count($indexedIdentifiers)) {
            return [];
        }

        $existingProductModelCodes = $this->connection->executeQuery(
            'SELECT code FROM pim_catalog_product_model WHERE code IN (:identifiers)',
            ['identifiers' => \array_values($indexedIdentifiers)],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return \array_keys(\array_diff($indexedIdentifiers, $existingProductModelCodes));
    }
}
