<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetEventSubscriptionLogsQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEventSubscriptionLogsQuery implements GetEventSubscriptionLogsQueryInterface
{
    const MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS = 100;
    const MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS = 72 * 60 * 60; // 72h

    private Client $elasticsearchClient;
    private Clock $clock;

    public function __construct(
        Client $elasticsearchClient,
        Clock $clock
    ) {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->clock = $clock;
    }

    public function execute(string $connectionCode): \Generator
    {
        $now = $this->clock->now();
        $lastNoticeOrInfoTimestamp = $this->findLastNoticeOrInfoTimestamp($connectionCode);

        return $this->elasticsearchClient->scroll([
            'sort' => [['timestamp' => ['order' => 'ASC']]],
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'bool' => [
                                'should' => [
                                    ['term' => ['connection_code' => $connectionCode]],
                                    ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
                                ],
                            ],
                        ],
                        [
                            'bool' => [

                            ]
                        ]
                    ],
//                        'should' => [
//                            [
//                                'bool' => [
//                                    'must' => [
//                                        ['terms' => ['level' => ['info', 'notice']]],
//                                        ['range' => ['timestamp' => ['gte' => $lastNoticeOrInfoTimestamp]]],
//                                        [
//                                            'bool' => [
//                                                'should' => [
//                                                    ['term' => ['connection_code' => $connectionCode]],
//                                                    ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                            [
//                                'bool' => [
//                                    'must' => [
//                                        ['terms' => ['level' => ['warning', 'error']]],
//                                        ['range' => ['timestamp' => ['gte' => $now->getTimestamp() - self::MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS]]],
//                                        [
//                                            'bool' => [
//                                                'should' => [
//                                                    ['term' => ['connection_code' => $connectionCode]],
//                                                    ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
                ],
            ],
        ]);
//        $results = $this->elasticsearchClient->scroll([
//            [], // metadata
//            [
//                'sort' => [['timestamp' => ['order' => 'ASC']]],
//                'size' => self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS,
//                'query' => [
//                    'bool' => [
//                        'must' => [
//                            ['terms' => ['level' => ['info', 'notice']]],
//                        ],
//                        'should' => [
//                            ['term' => ['connection_code' => $connectionCode]],
//                            ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
//                        ],
//                    ],
//                ],
//            ],
//            [], // metadata
//            [
//                'sort' => [['timestamp' => ['order' => 'ASC']]],
//                'query' => [
//                    'bool' => [
//                        'must' => [
//                            ['terms' => ['level' => ['warning', 'error']]],
//                            ['range' => ['timestamp' => ['gte' => $now->getTimestamp() - self::MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS]]],
//                            [
//                                'bool' => [
//                                    'should' => [
//                                        ['term' => ['connection_code' => $connectionCode]],
//                                        ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ]);

//        foreach ($results['responses'] as $response) {
//            foreach ($response['hits']['hits'] as $hit) {
//                yield $hit['_source'];
//            }
//        }

//        dump($results);

//        yield null;

//        yield null;

//        return $this->elasticsearchClient->scroll(
//            [
//                'sort' => [
//                    'timestamp' => [
//                        'order' => 'ASC',
//                    ],
//                ],
//                // TODO: ????
//                'size' => 11,
//                'query' => [
//                    'bool' => [
//                        'should' => [
//                            ['term' => ['connection_code' => $connectionCode],],
//                            ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code'],],],],
//                        ],
//                    ],
//                ],
//            ],
//            1000
//        );
    }

    private function findLastNoticeAndInfoIdentifiers(string $connectionCode): ?int
    {
        $result = $this->elasticsearchClient->search([
            'sort' => [['timestamp' => ['order' => 'ASC']]],
            'size' => 1,
            'from' => self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS,
            'query' => [
                'bool' => [
                    'must' => [
                        ['terms' => ['level' => ['info', 'notice']]],
                    ],
                    'should' => [
                        ['term' => ['connection_code' => $connectionCode]],
                        ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
                    ],
                ],
            ],
        ]);

        foreach ($result['hits']['hits'] as $hit) {
            return $hit['_source']['timestamp'];
        }

        return null;
    }
}
