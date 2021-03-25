<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEventsApiErrorLogsQuery
{
    private Client $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    public function execute(\DateTimeImmutable $olderThanDatetime): void
    {
        $this->esClient->deleteByQuery($this->getDeleteErrorDocumentsOlderThanTheGivenDateQuery($olderThanDatetime));
    }

    private function getDeleteErrorDocumentsOlderThanTheGivenDateQuery(\DateTimeImmutable $olderThanDatetime): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'terms' => [
                                'level' => [
                                    EventsApiDebugLogLevels::ERROR,
                                    EventsApiDebugLogLevels::WARNING
                                ],
                            ],
                        ],
                        ['range' => ['timestamp' => ['lt' => $olderThanDatetime->getTimestamp()]]],
                    ],
                ],
            ],
        ];
    }
}
