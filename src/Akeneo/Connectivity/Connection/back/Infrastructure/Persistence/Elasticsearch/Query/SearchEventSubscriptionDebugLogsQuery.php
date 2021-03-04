<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SearchEventSubscriptionDebugLogsQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEventSubscriptionDebugLogsQuery implements SearchEventSubscriptionDebugLogsQueryInterface
{
    const MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS = 100;
    const MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS = 72 * 60 * 60; // 72h

    private Client $elasticsearchClient;
    private Clock $clock;
    private PrimaryKeyEncrypter $encrypter;

    public function __construct(
        Client $elasticsearchClient,
        Clock $clock,
        PrimaryKeyEncrypter $encrypter
    ) {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->clock = $clock;
        $this->encrypter = $encrypter;
    }

    public function execute(string $connectionCode, ?string $encryptedSearchAfter = null): array
    {
        $parameters = [
            'search_after' => null,
            'first_notice_and_info_id' => null,
            'first_notice_and_info_search_after' => null,
        ];

        if (null !== $encryptedSearchAfter) {
            $decryptedSearchAfter = $this->encrypter->decrypt($encryptedSearchAfter);
            $parameters = json_decode($decryptedSearchAfter, true);
        }

        $nowTimestamp = $this->clock->now()->getTimestamp();

        if (null !== $parameters['first_notice_and_info_id']
            && null !== $parameters['first_notice_and_info_search_after']
        ) {
            $lastNoticeAndInfoIdentifiers = $this->findSameLastNoticeAndInfoIdentifiers(
                $connectionCode,
                $parameters['first_notice_and_info_id'],
                $parameters['first_notice_and_info_search_after']
            );
        } else {
            $lastNoticeAndInfoIdentifiers = $this->findLastNoticeAndInfoIdentifiers($connectionCode);
        }

        $query = [
            'size' => 25,
            'sort' => [
                'timestamp' => 'DESC',
                '_id' => 'ASC'
            ],
            'track_total_hits' => true,
            'query' => [
                'bool' => [
                    'should' => [
                        ['bool' => ['must' => [
                            ['terms' => ['level' => ['info', 'notice']]],
                            ['terms' => ['_id' => $lastNoticeAndInfoIdentifiers['identifiers']]],
                            ['bool' => ['should' => [
                                ['term' => ['connection_code' => $connectionCode]],
                                ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]], // connection_code IS NULL
                            ]]],
                        ]]],
                        ['bool' => ['must' => [
                            ['terms' => ['level' => ['error', 'warning']]],
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

        if (null !== $parameters['search_after']) {
            $query['search_after'] = $parameters['search_after'];
        }

        $result = $this->elasticsearchClient->search($query);

        return [
            'results' => array_map(function ($hit) {
                return $hit['_source'];
            }, $result['hits']['hits']),
            'search_after' => $this->encrypter->encrypt(json_encode([
                'search_after' => end($result['hits']['hits'])['sort'] ?? null,
                'first_notice_and_info_id' => $lastNoticeAndInfoIdentifiers['first_id'],
                'first_notice_and_info_search_after' => $lastNoticeAndInfoIdentifiers['first_sort'],
            ])),
            'total' => $result['hits']['total']['value'],
        ];
    }

    /**
     * @return array{
     *   first_id: ?string,
     *   first_sort: ?array<int, string>,
     *   identifiers: array<string>
     * }
     */
    private function findLastNoticeAndInfoIdentifiers(string $connectionCode): array
    {
        $result = $this->elasticsearchClient->search(
            [
                '_source' => ['id'],
                'sort' => [
                    'timestamp' => 'DESC',
                    '_id' => 'ASC'
                ],
                'size' => self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['terms' => ['level' => ['info', 'notice']]],
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

        return [
            'first_id' => $result['hits']['hits'][0]['_id'] ?? null,
            'first_sort' => $result['hits']['hits'][0]['sort'] ?? null,
            'identifiers' => array_map(function ($hit) {
                return $hit['_id'];
            }, $result['hits']['hits']),
        ];
    }

    /**
     * @return array{
     *   first_id: ?string,
     *   first_sort: ?array<int, string>,
     *   identifiers: array<string>
     * }
     */
    private function findSameLastNoticeAndInfoIdentifiers(string $connectionCode, string $firstId, array $firstSort): array
    {
        $result = $this->elasticsearchClient->search(
            [
                '_source' => ['id'],
                'sort' => [
                    'timestamp' => 'DESC',
                    '_id' => 'ASC'
                ],
                'search_after' => $firstSort,
                'size' => self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS - 1,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['terms' => ['level' => ['info', 'notice']]],
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

        return [
            'first_id' => $firstId,
            'first_sort' => $firstSort,
            'identifiers' => array_merge([$firstId], array_map(function ($hit) {
                return $hit['_id'];
            }, $result['hits']['hits'])),
        ];
    }
}
