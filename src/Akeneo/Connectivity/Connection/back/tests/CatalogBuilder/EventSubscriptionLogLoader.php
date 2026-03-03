<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\ElasticsearchEventsApiDebugRepository;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventSubscriptionLogLoader
{
    public function __construct(private ElasticsearchEventsApiDebugRepository $elasticsearchEventsApiDebugRepository)
    {
    }

    /**
     * @var array{
     *  array{
     *    timestamp: int,
     *    level: string,
     *    message: string,
     *    connection_code: ?string,
     *    context: array
     *   }
     * } $logs
     */
    public function bulkInsert(array $logs): void
    {
        foreach ($logs as $log) {
            $this->elasticsearchEventsApiDebugRepository->persist($log);
        }

        $this->elasticsearchEventsApiDebugRepository->flush();
    }
}
