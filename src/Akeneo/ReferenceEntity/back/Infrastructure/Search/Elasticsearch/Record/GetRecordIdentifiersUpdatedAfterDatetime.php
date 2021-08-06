<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

class GetRecordIdentifiersUpdatedAfterDatetime
{
    public function nextBatch(Client $elasticsearchClient, \DateTimeInterface $dateTime, int $batchSize): iterable
    {
        $body = [
            'size' => $batchSize,
            '_source' => [
                'identifier',
            ],
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'range' => [
                                        'updated_at' => ['gt' => $dateTime->getTimestamp()]
                                    ]
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            'sort' => [
                'code' => 'asc',
            ],
        ];

        $rows = $elasticsearchClient->search($body);
        while (!empty($rows['hits']['hits'])) {
            yield array_map(function (array $product) {
                return $product['_source']['identifier'];
            }, $rows['hits']['hits']);

            $body['search_after'] = end($rows['hits']['hits'])['sort'];
            $rows = $elasticsearchClient->search($body);
        }
    }
}
