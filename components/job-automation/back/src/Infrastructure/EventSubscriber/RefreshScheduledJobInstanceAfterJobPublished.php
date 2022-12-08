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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\UpdateAutomationLastExecutionDateQueryInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RefreshScheduledJobInstanceAfterJobPublished implements EventSubscriberInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UpdateAutomationLastExecutionDateQueryInterface $updateJobInstanceAutomationLastExecutionDate,
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
        if (null !== $event->getJobExecution()->getUser() && str_contains($event->getJobExecution()->getUser(), ResolveScheduledJobRunningUsername::AUTOMATED_USER_PREFIX)) {
            $lastExecutionDate = $this->clock->now();
            $this->updateJobInstanceAutomationLastExecutionDate->forJobInstanceCode(
                $event->getJobExecution()->getJobInstance()->getCode(),
                $lastExecutionDate,
            );
        }
    }
}
