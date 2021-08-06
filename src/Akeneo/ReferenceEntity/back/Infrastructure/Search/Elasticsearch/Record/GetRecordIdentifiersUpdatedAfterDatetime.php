<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetRecordIdentifiersUpdatedAfterDatetime
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
