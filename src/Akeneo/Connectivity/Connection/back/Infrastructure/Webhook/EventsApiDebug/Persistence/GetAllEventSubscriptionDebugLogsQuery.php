<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAllEventSubscriptionDebugLogsQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllEventSubscriptionDebugLogsQuery implements GetAllEventSubscriptionDebugLogsQueryInterface
{
    const MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS = 100;
    const MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS = 72 * 60 * 60; // 72h

    private Client $elasticsearchClient;
    private ClockInterface $clock;

    public function __construct(
        Client $elasticsearchClient,
        ClockInterface $clock
    ) {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->clock = $clock;
    }

    public function execute(string $connectionCode): \Generator
    {
        $nowTimestamp = $this->clock->now()->getTimestamp();
        $lastNoticeAndInfoIdentifiers = \iterator_to_array($this->findLastNoticeAndInfoIdentifiers($connectionCode));

        $query = [
            'size' => 1000,
            'sort' => [
                'timestamp' => 'ASC',
                'id' => 'ASC'
            ],
            'query' => [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [
                            ['terms' => ['level' => [EventsApiDebugLogLevels::NOTICE, EventsApiDebugLogLevels::INFO]]],
                            ['terms' => ['id' => $lastNoticeAndInfoIdentifiers]],
                            ['bool' => ['should' => [
                                ['term' => ['connection_code' => $connectionCode]],
                                ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]], // connection_code IS NULL
                            ]]],
                        ]]],
                        ['bool' => ['must' => [
                            ['terms' => ['level' => [EventsApiDebugLogLevels::ERROR, EventsApiDebugLogLevels::WARNING]]],
                            ['range' => ['timestamp' => ['gte' => $nowTimestamp - self::MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS]]],
                            ['bool' => ['should' => [
                                ['term' => ['connection_code' => $connectionCode]],
                                ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]], // connection_code IS NULL
                            ]]],
                        ]]],
                    ],
                ],
            ],
        ];

        $rows = $this->elasticsearchClient->search($query);

        while (!empty($rows['hits']['hits'])) {
            foreach ($rows['hits']['hits'] as $hit) {
                yield $hit['_source'];
            }
            $query['search_after'] = \end($rows['hits']['hits'])['sort'];

            $rows = $this->elasticsearchClient->search($query);
        }
    }

    private function findLastNoticeAndInfoIdentifiers(string $connectionCode): \Generator
    {
        $result = $this->elasticsearchClient->search(
            [
                '_source' => ['id'],
                'sort' => [['timestamp' => ['order' => 'DESC']]],
                'size' => self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['terms' => ['level' => [EventsApiDebugLogLevels::INFO, EventsApiDebugLogLevels::NOTICE]]],
                            [
                                'bool' => [
                                    'should' => [
                                        ['term' => ['connection_code' => $connectionCode]],
                                        ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        foreach ($result['hits']['hits'] as $hit) {
            yield $hit['_source']['id'];
        }
    }
}
