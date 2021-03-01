<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\EventsApiDebugLogLevels;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLogger
{
    private int $bufferSize;

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

    private Clock $clock;

    private EventsApiDebugRepository $repository;

    public function __construct(EventsApiDebugRepository $repository, Clock $clock, int $bufferSize = 100)
    {
        $this->repository = $repository;
        $this->clock = $clock;
        $this->bufferSize = $bufferSize;
        $this->buffer = [];
    }

    public function logLimitOfEventApiRequestsReached(): void
    {
        $this->addLog([
            'timestamp' => $this->clock->now()->getTimestamp(),
            'level' => EventsApiDebugLogLevels::WARNING,
            'message' => 'The maximum number of events sent per hour has been reached.',
            'connection_code' => null,
            'context' => [],
        ]);
    }

    public function flushLogs(): void
    {
        if (0 === count($this->buffer)) {
            return;
        }

        $this->repository->bulkInsert($this->buffer);
        $this->buffer = [];
    }

    /**
     * @param array{
     *  timestamp: int,
     *  level: string,
     *  message: string,
     *  connection_code: ?string,
     *  context: array
     * } $log
     */
    private function addLog(array $log): void
    {
        $this->buffer[] = $log;

        if (count($this->buffer) >= $this->bufferSize) {
            $this->flushLogs();
        }
    }
}
