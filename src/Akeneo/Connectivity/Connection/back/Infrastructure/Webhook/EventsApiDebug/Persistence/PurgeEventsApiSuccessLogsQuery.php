<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEventsApiSuccessLogsQuery
{
    public function __construct(private Client $esClient)
    {
    }

    public function execute(int $nbOfNoticesAndInfosToKeep = 100): void
    {
        $search = $this->getEsIdsToKeepQuery($nbOfNoticesAndInfosToKeep);

        $result = $this->esClient->search($search);
        $esIdsToKeep = [];
        foreach ($result['hits']['hits'] as $hit) {
            $esIdsToKeep[] = $hit['_source']['id'];
        }
        $this->esClient->deleteByQuery($this->getDeleteAllDocumentsButGivenIdsQuery($esIdsToKeep));
    }

    /**
     * @return array{query: array{bool: array{must_not: array{terms: array{id: mixed[]}}, must: array{terms: array{level: string[]}}}}}
     */
    private function getDeleteAllDocumentsButGivenIdsQuery(array $idsToKeep): array
    {
        return [
            'query' => [
                'bool' => [
                    'must_not' => ['terms' => ['id' => $idsToKeep]],
                    'must' => [
                        'terms' => [
                            'level' => [
                                EventsApiDebugLogLevels::INFO,
                                EventsApiDebugLogLevels::NOTICE,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{_source: string[], sort: array<int, array{timestamp: array{order: string}}>, size: int, query: array{constant_score: array{filter: array{bool: array{filter: array{exists: array{field: string}}[]|array{terms: array{level: string[]}}[]}}}}}
     */
    private function getEsIdsToKeepQuery(int $nbOfNoticesAndInfosToKeep): array
    {
        return [
            '_source' => ['id'],
            'sort' => [['timestamp' => ['order' => 'DESC']]],
            'size' => $nbOfNoticesAndInfosToKeep,
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                ['exists' => ['field' => 'id']],
                                [
                                    'terms' => [
                                        'level' => [
                                            EventsApiDebugLogLevels::INFO,
                                            EventsApiDebugLogLevels::NOTICE,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
