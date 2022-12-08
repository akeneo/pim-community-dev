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
    private Client $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
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
