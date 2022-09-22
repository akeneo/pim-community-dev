<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Publisher;

use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Publisher\RetryPublisherInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

final class RetryPublisher implements RetryPublisherInterface
{
    private const MAX_RETRY = 1;
    private const RETRY_DELAY_IN_MILLISECOND = 1000;

    public function __construct(
        private ClockInterface $clock,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function publish(callable $pushJob, DueJobInstance $dueJobInstance): void
    {
        $shouldRetry = false;
        $retryCount = 0;

        do {
            try {
                call_user_func($pushJob, $dueJobInstance);
            } catch (\Exception) {
                $shouldRetry = self::MAX_RETRY > $retryCount;
                if ($shouldRetry) {
                    $exponentialBackoff = self::RETRY_DELAY_IN_MILLISECOND * ($retryCount + 1);
                    $this->clock->sleep($exponentialBackoff);
                    ++$retryCount;
                    continue;
                }

                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInternalError($dueJobInstance),
                );

                $this->logger->error('Cannot launch scheduled job due to an infrastructure error', [
                    'error_message' => $exception->getMessage(),
                ]);
            }
        } while ($shouldRetry);
    }
}
