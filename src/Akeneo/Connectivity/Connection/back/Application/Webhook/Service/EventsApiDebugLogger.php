<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLogger
{
    const LEVEL_NOTICE = 'notice';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    private int $bufferSize;

    /**
     * @var array<array{
     *  timestamp: int,
     *  level: self::LEVEL_*,
     *  message: string,
     *  connection_code: ?string,
     *  context: array
     * }>
     */
    private array $buffer;

    private EventsApiDebugRepository $repository;

    public function __construct(EventsApiDebugRepository $repository, int $bufferSize = 100)
    {
        $this->repository = $repository;
        $this->bufferSize = $bufferSize;
        $this->buffer = [];
    }

    public function logLimitOfEventApiRequestsReached(int $timestamp = null): void
    {
        $this->addLog([
            'timestamp' => $timestamp ?? time(),
            'level' => self::LEVEL_WARNING,
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
     *  level: self::LEVEL_*,
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
