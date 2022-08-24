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

namespace Akeneo\Platform\JobAutomation\Infrastructure\EventSubscriber;

use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionCommand;
use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionHandler;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RefreshScheduledJobInstanceAfterJobPublished implements EventSubscriberInterface
{
    public function __construct(
        private UpdateScheduledJobInstanceLastExecutionHandler $updateScheduledJobInstanceLastExecutionHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::JOB_EXECUTION_CREATED => 'refreshScheduledJobInstance',
        ];
    }

    public function refreshScheduledJobInstance(JobExecutionEvent $event): void
    {
        $jobInstanceCode = $event->getJobExecution()->getJobInstance()->getCode();
        $command = new UpdateScheduledJobInstanceLastExecutionCommand($jobInstanceCode);
        $this->updateScheduledJobInstanceLastExecutionHandler->handle($command);
    }
}
