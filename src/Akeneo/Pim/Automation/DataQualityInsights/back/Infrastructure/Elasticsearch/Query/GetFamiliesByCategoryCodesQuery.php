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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamiliesByCategoryCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetFamiliesByCategoryCodesQuery implements GetFamiliesByCategoryCodesQueryInterface
{
    private Client $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    public function execute(array $categoryCodes): array
    {
        if (empty($categoryCodes)) {
            return [];
        }

        $query = [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'document_type' => ProductInterface::class
                            ],
                        ],
                        [
                            'terms' => [
                                'categories' => $categoryCodes,
                            ],
                        ],
                    ],
                ],
            ],
            'aggs' => [
                'families' => [
                    'terms' => [
                        'field' => 'family.code',
                    ],
                ],
            ],
        ];

        $result = $this->esClient->search($query);

        $families = [];
        foreach ($result['aggregations']['families']['buckets'] ?? [] as $aggregation) {
            if (isset($aggregation['key'])) {
                $families[] = $aggregation['key'];
            }
        }

        return $families;
    }
}
