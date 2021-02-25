<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEventsApiLogsQuery
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    /**
     * @param array $connectionCodes Connection codes for which we want to keep errors
     * @param int $nbOfNoticesAndInfosToKeep  Number of notices and infos to keep for each connection
     * @param int $nbOfDaysToKeep    Age of errors to keep
     */
    public function execute(int $nbOfNoticesAndInfosToKeep = 100): void
    {
        $search = $this->getEsIdsToKeepQuery($nbOfNoticesAndInfosToKeep);

        $result = $this->esClient->search($search);
        $esIdsToKeep = [];
        foreach ($result['hits']['hits'] as $hit) {
            $esIdsToKeep[] = $hit['_id'];
        }
        $this->esClient->deleteByQuery($this->getDeleteAllDocumentsButGivenIdsQuery($esIdsToKeep));
    }

    private function getDeleteAllDocumentsButGivenIdsQuery(array $idsToKeep): array
    {
        return [
            'query' => [
                'bool' => [
                    'must_not' => ['terms' => ['_id' => $idsToKeep]]
                ]
            ]
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
                                'terms' => ['level' => ['info', 'notice']],
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
