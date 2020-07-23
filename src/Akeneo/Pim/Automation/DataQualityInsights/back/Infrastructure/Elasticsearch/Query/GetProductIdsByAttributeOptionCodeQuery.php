<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsByAttributeOptionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetProductIdsByAttributeOptionCodeQuery implements GetProductIdsByAttributeOptionCodeQueryInterface
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    public function execute(AttributeOptionCode $attributeOptionCode, int $bulkSize): \Iterator
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
                        'query_string' => [
                            'default_field' => sprintf('values.%s-option*', $attributeOptionCode->getAttributeCode()),
                            'query' => strval($attributeOptionCode)
                        ]
                    ],
                ],
            ],
        ];
        $searchQuery = [
            '_source' => ['id'],
            'size' => $bulkSize,
            'sort' => ['_id' => 'asc'],
            'query' => $query,
        ];

        $totalProducts = $this->countTotalOfProducts($query);
        $result = $this->esClient->search($searchQuery);
        $searchAfter = [];
        $returnedProducts = 0;

        while (!empty($result['hits']['hits'])) {
            $productIds = [];
            foreach ($result['hits']['hits'] as $product) {
                $productIds[] = new ProductId(intval(str_replace('product_', '', $product['_source']['id'])));
                $searchAfter = $product['sort'];
            }

            yield $productIds;

            $returnedProducts += count($productIds);
            $result = $returnedProducts < $totalProducts ? $this->searchAfter($searchQuery, $searchAfter) : [];
        }
    }

    private function searchAfter(array $query, array $searchAfter): array
    {
        if (!empty($searchAfter)) {
            $query['search_after'] = $searchAfter;
        }

        return $this->esClient->search($query);
    }

    private function countTotalOfProducts(array $query): int
    {
        $countResult = $this->esClient->count(['query' => $query]);

        if (!isset($countResult['count'])) {
            throw new \Exception('Failed to count the total number of products by attribute option');
        }

        return intval($countResult['count']);
    }
}
