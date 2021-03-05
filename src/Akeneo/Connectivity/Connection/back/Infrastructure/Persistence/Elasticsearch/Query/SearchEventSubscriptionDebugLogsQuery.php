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
            $lastNoticeAndInfoIds = $this->findSameLastNoticeAndInfoIds(
                $connectionCode,
                $parameters['first_notice_and_info_id'],
                $parameters['first_notice_and_info_search_after']
            );
        } else {
            $lastNoticeAndInfoIds = $this->findLastNoticeAndInfoIds($connectionCode);
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
                            ['terms' => ['_id' => $lastNoticeAndInfoIds['ids']]],
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
                'first_notice_and_info_id' => $lastNoticeAndInfoIds['first_id'],
                'first_notice_and_info_search_after' => $lastNoticeAndInfoIds['first_search_after'],
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

    private function getLastNoticeAndInfoIdsQuery(string $connectionCode): array
    {
        return [
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
            'first_id' => $result['hits']['hits'][0]['_id'] ?? null,
            'first_search_after' => $result['hits']['hits'][0]['sort'] ?? null,
            'ids' => array_map(function ($hit) {
                return $hit['_id'];
            }, $result['hits']['hits']),
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
    private function findSameLastNoticeAndInfoIds(string $connectionCode, string $firstId, array $firstSearchAfter): array
    {
        $query = $this->getLastNoticeAndInfoIdsQuery($connectionCode);
        $query['search_after'] = $firstSearchAfter;
        $query['size'] = self::MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS - 1;

        $result = $this->elasticsearchClient->search($query);

        return [
            'first_id' => $firstId,
            'first_search_after' => $firstSearchAfter,
            'ids' => array_merge([$firstId], array_map(function ($hit) {
                return $hit['_id'];
            }, $result['hits']['hits'])),
        ];
    }
}
