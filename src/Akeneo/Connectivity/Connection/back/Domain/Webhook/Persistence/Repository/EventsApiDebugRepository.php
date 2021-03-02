<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventsApiDebugRepository
{
    /**
     * Tells the repository to make a log entry persistent.
     *
     * Invoking the persist method does NOT save the log immediately,
     * you need to call the flush method to effectively do it.
     *
     * @param array{
     *  timestamp: int,
     *  level: string,
     *  message: string,
     *  connection_code: ?string,
     *  context: array
     * } $log
     */
    public function persist(array $log): void;

    /**
     * Saves all the log entries that have been queued up to now.
     */
    public function flush(): void;
}
