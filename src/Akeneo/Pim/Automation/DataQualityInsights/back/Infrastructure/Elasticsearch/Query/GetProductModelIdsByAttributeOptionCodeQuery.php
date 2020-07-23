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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetProductModelIdsByAttributeOptionCodeQuery implements GetProductIdsByAttributeOptionCodeQueryInterface
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
                                'document_type' => ProductModelInterface::class
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

        $totalProductModels = $this->countTotalOfProductModels($query);
        $result = $this->esClient->search($searchQuery);

        $searchAfter = [];
        $returnedProductModels = 0;

        while (!empty($result['hits']['hits'])) {
            $productModelIds = [];
            foreach ($result['hits']['hits'] as $product) {
                $productModelIds[] = new ProductId(intval(str_replace('product_model_', '', $product['_source']['id'])));
                $searchAfter = $product['sort'];
            }

            yield $productModelIds;

            $returnedProductModels += count($productModelIds);
            $result = $returnedProductModels < $totalProductModels ? $this->searchAfter($searchQuery, $searchAfter) : [];
        }
    }

    private function searchAfter(array $query, array $searchAfter): array
    {
        if (!empty($searchAfter)) {
            $query['search_after'] = $searchAfter;
        }

        return $this->esClient->search($query);
    }

    private function countTotalOfProductModels(array $query): int
    {
        $countResult = $this->esClient->count(['query' => $query]);

        if (!isset($countResult['count'])) {
            throw new \Exception('Failed to count the total number of product models by attribute option');
        }

        return intval($countResult['count']);
    }
}
