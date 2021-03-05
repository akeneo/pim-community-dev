<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SearchEventSubscriptionDebugLogsQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The functionnal need of this query is not easy to do in ES.
 * The query is working and well tested but not easy to read at first glance.
 *
 * We want the logs `warning` and `error` of the last 72h.
 * We want the last 100 `notice` and `info`.
 * We want to have those 2 in the same list, ordered by date DESC.
 *
 * Since you cannot write subqueries in ES, we simply run the subquery first.
 * We know it's easier to write its counterpart in pseudo-SQL, so here it is:
 *
 * $noticeIds = <<<
 *   SELECT id
 *   FROM logs
 *   WHERE (level = "info" OR level = "notice")
 *   ORDER BY timestamp DESC
 *   LIMIT 100;
 *
 * $logs = <<<
 *   SELECT * FROM logs
 *   WHERE
 *    (id IN :ids)
 *    OR (
 *      (level = "error" OR level = "warning")
 *       AND timestamp > ${now - 72h}
 *    )
 *   ORDER BY timestamp DESC
 *   LIMIT 25;
 *
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
        $parameters = $this->buildParameters($encryptedSearchAfter);

        $nowTimestamp = $this->clock->now()->getTimestamp();

        if (null !== $parameters['first_notice_and_info_id'] && null !== $parameters['first_notice_and_info_search_after']) {
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
                            ['terms' => ['_id' => $lastNoticeAndInfoIdentifiers['identifiers']]],
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
     *   search_after: ?array<string>,
     *   first_notice_and_info_id: ?string,
     *   first_notice_and_info_search_after: ?array<string>,
     * }
     */
    private function buildParameters(?string $encryptedSearchAfter): array
    {
        $defaults = [
            'search_after' => null,
            'first_notice_and_info_id' => null,
            'first_notice_and_info_search_after' => null,
        ];

        if (null === $encryptedSearchAfter) {
            return $defaults;
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults($defaults);

        $decryptedSearchAfter = $this->encrypter->decrypt($encryptedSearchAfter);
        $parameters = json_decode($decryptedSearchAfter, true);

        return $resolver->resolve($parameters);
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
