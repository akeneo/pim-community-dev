<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SearchEventSubscriptionDebugLogsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Encrypter;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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
    private ClockInterface $clock;
    private Encrypter $encrypter;

    public function __construct(
        Client $elasticsearchClient,
        ClockInterface $clock,
        Encrypter $encrypter
    ) {
        $this->elasticsearchClient = $elasticsearchClient;
        $this->clock = $clock;
        $this->encrypter = $encrypter;
    }

    public function execute(string $connectionCode, ?string $encryptedSearchAfter = null, array $filters = []): array
    {
        $filters = $this->resolveFilters($filters);
        $parameters = $this->buildParameters($encryptedSearchAfter);

        if (null !== $parameters['first_notice_and_info_id']
            && null !== $parameters['first_notice_and_info_search_after']
        ) {
            $lastNoticeAndInfoIds = $this->findSameLastNoticeAndInfoIds(
                $connectionCode,
                $parameters['first_notice_and_info_id'],
                $parameters['first_notice_and_info_search_after']
            );
        } else {
            $lastNoticeAndInfoIds = $this->findLastNoticeAndInfoIds($connectionCode);
        }

        $query = $this->buildQuery($connectionCode, $parameters['search_after'], $lastNoticeAndInfoIds, $filters);

        $result = $this->elasticsearchClient->search($query);

        return [
            'results' => \array_map(
                function ($hit) {
                    return $hit['_source'];
                },
                $result['hits']['hits']
            ),
            'search_after' => $this->encrypter->encrypt(
                \json_encode(
                    [
                        'search_after' => \end($result['hits']['hits'])['sort'] ?? null,
                        'first_notice_and_info_id' => $lastNoticeAndInfoIds['first_id'],
                        'first_notice_and_info_search_after' => $lastNoticeAndInfoIds['first_search_after'],
                    ]
                )
            ),
            'total' => $result['hits']['total']['value'],
        ];
    }

    /**
     * @param array{
     *   first_id: ?string,
     *   first_search_after: ?array<string>,
     *   ids: array<string>
     * } $lastNoticeAndInfoIds
     * @param null|array<mixed> $searchAfter
     * @param array{
     *  levels?: array,
     *  timestamp_from?: int,
     *  timestamp_to?: int,
     *  text?: string,
     * } $filters
     * @return array<mixed>
     */
    private function buildQuery(
        string $connectionCode,
        ?array $searchAfter,
        array $lastNoticeAndInfoIds,
        array $filters
    ): array {
        $nowTimestamp = $this->clock->now()->getTimestamp();
        $constraints = [
            ['exists' => ['field' => 'id']],
            [
                'bool' => [
                    'should' => [
                        [
                            'bool' => [
                                'must' => [
                                    ['terms' => ['id' => $lastNoticeAndInfoIds['ids']]],
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'must' => [
                                    [
                                        'terms' => [
                                            'level' => [
                                                EventsApiDebugLogLevels::ERROR,
                                                EventsApiDebugLogLevels::WARNING,
                                            ],
                                        ],
                                    ],
                                    ['range' => ['timestamp' => ['gte' => $nowTimestamp - self::MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS]]],
                                    [
                                        'bool' => [
                                            'should' => [
                                                ['term' => ['connection_code' => $connectionCode]],
                                                ['bool' => ['must_not' => ['exists' => ['field' => 'connection_code']]]], // connection_code IS NULL
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        if (null !== $filters['levels']) {
            $constraints[] = [
                'terms' => [
                    'level' => $filters['levels'],
                ],
            ];
        }

        if (null !== $filters['timestamp_from']) {
            $constraints[] = [
                'range' => [
                    'timestamp' => [
                        'gte' => $filters['timestamp_from'],
                    ],
                ],
            ];
        }

        if (null !== $filters['timestamp_to']) {
            $constraints[] = [
                'range' => [
                    'timestamp' => [
                        'lte' => $filters['timestamp_to'],
                    ],
                ],
            ];
        }

        if (null !== $filters['text']) {
            $constraints[] = [
                'query_string' => [
                    'fields' => ['message', 'context_flattened'],
                    'query'=> $this->formatTextSearch(\strtolower($filters['text'])),
                    'fuzziness' => 0,
                    'default_operator' => 'AND'
                ],
            ];
        }

        $query = [
            'size' => 25,
            'sort' => [
                'timestamp' => 'DESC',
                'id' => 'ASC',
            ],
            'track_total_hits' => true,
            'query' => [
                'bool' => [
                    'filter' => $constraints,
                ],
            ],
        ];

        if (null !== $searchAfter) {
            $query['search_after'] = $searchAfter;
        }

        return $query;
    }

    private function formatTextSearch(string $value): string
    {
        $regex = '#[-+=|!&(){}\[\]^"~*<>?:/\\\]#';

        $escaped =  \preg_replace($regex, '\\\$0', $value);
        $split = \preg_split('/ /', $escaped);
        $formatted = '';
        foreach ($split as $item) {
            $formatted .= \sprintf('*%s* ', $item);
        }

        return $formatted;
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

        $decryptedSearchAfter = $this->encrypter->decrypt($encryptedSearchAfter);
        $parameters = \json_decode($decryptedSearchAfter, true);

        return $parameters;
    }

    private function getLastNoticeAndInfoIdsQuery(string $connectionCode): array
    {
        return [
            '_source' => ['id'],
            'sort' => [
                'timestamp' => 'DESC',
                'id' => 'ASC',
            ],
            'size' => self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS,
            'query' => [
                'bool' => [
                    'must' => [
                        ['exists' => ['field' => 'id']],
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
        ];
    }

    /**
     * @return array{
     *   first_id: ?string,
     *   first_search_after: ?array<string>,
     *   ids: array<string>
     * }
     */
    private function findLastNoticeAndInfoIds(string $connectionCode): array
    {
        $query = $this->getLastNoticeAndInfoIdsQuery($connectionCode);

        $result = $this->elasticsearchClient->search($query);

        return [
            'first_id' => $result['hits']['hits'][0]['_source']['id'] ?? null,
            'first_search_after' => $result['hits']['hits'][0]['sort'] ?? null,
            'ids' => \array_map(
                fn ($hit) => $hit['_source']['id'],
                $result['hits']['hits']
            ),
        ];
    }

    /**
     * To avoid duplicated results, when using the pagination, we want to repeat the same initial list of 100 ids
     * for the requests paginating after the first page.
     *
     * To do so, we reuse the "id" and the "search_after" of the *first* result of the initial query.
     * We can then repeat the results by doing a search_after ES query, asking for the 99 results after the
     * first one we already know.
     * We then merge the first id and the 99 following ids, and we have the same 100 ids.
     *
     * @return array{
     *   first_id: ?string,
     *   first_search_after: ?array<string>,
     *   ids: array<string>
     * }
     */
    private function findSameLastNoticeAndInfoIds(
        string $connectionCode,
        string $firstId,
        array $firstSearchAfter
    ): array {
        $query = $this->getLastNoticeAndInfoIdsQuery($connectionCode);
        $query['search_after'] = $firstSearchAfter;
        $query['size'] = self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS - 1;

        $result = $this->elasticsearchClient->search($query);

        return [
            'first_id' => $firstId,
            'first_search_after' => $firstSearchAfter,
            'ids' => \array_merge(
                [$firstId],
                \array_map(
                    fn ($hit) => $hit['_source']['id'],
                    $result['hits']['hits']
                )
            ),
        ];
    }

    private function resolveFilters(array $filters): array
    {
        $resolver = new OptionsResolver();

        $resolver->setDefault('levels', null);
        $resolver->setDefault('timestamp_from', null);
        $resolver->setDefault('timestamp_to', null);
        $resolver->setDefault('text', null);

        $resolver->setAllowedTypes('levels', ['null', 'string[]']);
        $resolver->setAllowedTypes('timestamp_from', ['null', 'int']);
        $resolver->setAllowedTypes('timestamp_to', ['null', 'int']);
        $resolver->setAllowedTypes('text', ['null', 'string']);

        $resolver->setAllowedValues(
            'levels',
            function ($levels) {
                if (null === $levels) {
                    return true;
                }

                if (!\is_array($levels)) {
                    return false;
                }

                foreach ($levels as $level) {
                    if (!\in_array($level, EventsApiDebugLogLevels::ALL)) {
                        return false;
                    }
                }

                return true;
            }
        );

        // If all the levels are selected, replace by `null`
        $resolver->setNormalizer('levels', function ($options, $value) {
            if (\is_array($value) && empty(\array_diff(EventsApiDebugLogLevels::ALL, $value))) {
                return null;
            }

            return $value;
        });
        $resolver->setNormalizer('text', function ($options, $value) {
            if (\is_string($value) && '' === \trim($value)) {
                return null;
            }

            return $value;
        });

        return $resolver->resolve($filters);
    }
}
