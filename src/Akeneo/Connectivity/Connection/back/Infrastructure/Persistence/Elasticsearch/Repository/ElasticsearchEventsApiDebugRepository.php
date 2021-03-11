<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Repository;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ElasticsearchEventsApiDebugRepository implements EventsApiDebugRepository
{
    private Client $client;

    /**
     * @var array<array{
     *  timestamp: int,
     *  level: string,
     *  message: string,
     *  connection_code: ?string,
     *  context: array
     * }>
     */
    private array $buffer;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->buffer = [];
    }

    public function persist(array $log): void
    {
        $flattenedContext = '';

        array_walk_recursive($log['context'], function($value, $key) use (&$flattenedContext) {
            $flattenedContext .= $value . ' ';
        });

        $log['context_flattened'] = trim($flattenedContext);

        $this->buffer[] = $log;
    }

    public function flush(): void
    {
        if (0 === count($this->buffer)) {
            return;
        }

        $this->client->bulkIndexes($this->buffer);
        $this->buffer = [];
    }
}
