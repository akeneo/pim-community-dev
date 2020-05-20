<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeConnectionErrorsQuery
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    /**
     * @param array $connectionCodes Connection codes for which we want to keep errors
     * @param int $nbOfErrorsToKeep  Number of errors to keep for each connection
     * @param int $nbOfDaysToKeep    Age of errors to keep
     */
    public function execute(array $connectionCodes, int $nbOfErrorsToKeep = 100, int $nbOfDaysToKeep = 8): void
    {
        $msearch = [];
        foreach ($connectionCodes as $code) {
            $msearch[] = [];
            $msearch[] = $this->getEsIdsToKeepForGivenConnectionQuery($code, $nbOfErrorsToKeep, $nbOfDaysToKeep);
        }

        $msearchResults = $this->esClient->msearch($msearch);
        $esIdsToKeep = [];
        foreach ($msearchResults['responses'] as $result) {
            foreach ($result['hits']['hits'] as $hit) {
                $esIdsToKeep[] = $hit['_id'];
            }
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

    private function getEsIdsToKeepForGivenConnectionQuery(
        string $code,
        int $nbOfErrorsToKeep,
        int $nbOfDaysToKeep
    ): array {
        $daysToKeepQuery = sprintf('now-%sd', $nbOfDaysToKeep);

        return [
            '_source' => ['id'],
            'sort' => [['error_datetime' => ['order' => 'DESC']]],
            'size' => $nbOfErrorsToKeep,
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => ['term' => ['connection_code' => $code]],
                            'must_not' => [
                                'range' => ['error_datetime' => ['lt' => $daysToKeepQuery]]
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
