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
class PurgeEventsApiErrorLogsQuery
{
    public function __construct(private Client $esClient)
    {
    }

    public function execute(\DateTimeImmutable $olderThanDatetime): void
    {
        $this->esClient->deleteByQuery($this->getDeleteErrorDocumentsOlderThanTheGivenDateQuery($olderThanDatetime));
    }

    /**
     * @return array{
     *     query: array{
     *         bool: array{
     *             must: array{terms: array{level: class-string<\error>[]|string[]}}[]
     *              |array{range: array{timestamp: array{lt: int}}}[]
     *         }
     *     }
     * }
     */
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
