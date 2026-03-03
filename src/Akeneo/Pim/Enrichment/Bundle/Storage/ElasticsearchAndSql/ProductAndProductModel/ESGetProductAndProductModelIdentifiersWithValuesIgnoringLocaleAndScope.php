<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResults;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ESGetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope implements GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope
{
    /** @var Client */
    private $client;

    /** @var int */
    private $batchSize;

    public function __construct(Client $client, int $batchSize)
    {
        $this->client = $client;
        $this->batchSize = $batchSize;
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function forAttributeAndValues(string $attributeCode, string $backendType, array $values): iterable
    {
        $attributePath = sprintf('values.%s-%s.*', $attributeCode, $backendType);

        $query = ([] === $values) ? '*' : implode(' OR ', $values);

        $baseQuery = [
            '_source' => ['id', 'identifier', 'document_type'],
            'size' => $this->batchSize,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'query_string' => [
                                'query' => $query,
                                'fields' => [$attributePath],
                            ],
                        ],
                        [
                            'term' => [
                                'attributes_for_this_level' => $attributeCode,
                            ],
                        ],
                    ],
                ],
            ],
            'sort' => ['id' => 'asc'],
        ];

        $searchAfter = null;
        while (true) {
            $query = $baseQuery;
            if ($searchAfter !== null) {
                $query['search_after'] = $searchAfter;
            }

            $response = $this->client->search($query);

            $hits = $response['hits']['hits'] ?? [];
            if (0 === count($hits)) {
                break;
            }

            $identifiers = new IdentifierResults();
            foreach ($hits as $hit) {
                $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type'], $hit['_source']['id']);
            }
            yield $identifiers;

            $lastResult = end($hits);
            $searchAfter = $lastResult['sort'];
        }
    }
}
