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
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RetryPublisher implements RetryPublisherInterface
{
    private const MAX_RETRY = 1;
    private const RETRY_DELAY_IN_MILLISECOND = 1000;

    public function __construct(
        private ClockInterface $clock,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
        private PublishJobToQueueInterface $publishJobToQueue,
    ) {
    }

    public function publish(DueJobInstance $dueJobInstance): void
    {
        $shouldRetry = false;
        $retryCount = 0;

        do {
            try {
                $this->pushJob($dueJobInstance);
            } catch (\Exception $exception) {
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

    private function pushJob(DueJobInstance $dueJobInstance): void
    {
        try {
            $this->publishJobToQueue->publish(
                jobInstanceCode: $dueJobInstance->scheduledJobInstance->code,
                config: [
                    'is_user_authenticated' => true,
                    'users_to_notify' => $dueJobInstance->usersToNotify->getUsernames(),
                ],
                username: $dueJobInstance->scheduledJobInstance->runningUsername,
                emails: $dueJobInstance->usersToNotify->getUniqueEmails(),
            );
        } catch (InvalidJobException $exception) {
            $errorMessages = array_map(
                static fn (ConstraintViolationInterface $constraintViolation) => $constraintViolation->getMessage(),
                iterator_to_array($exception->getViolations()),
            );

            $this->eventDispatcher->dispatch(
                CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance($dueJobInstance, $errorMessages),
            );
        }
    }
}
