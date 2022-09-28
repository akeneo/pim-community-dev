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

namespace Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue;

use Akeneo\Platform\JobAutomation\Domain\CronExpressionFactory;
use Akeneo\Platform\JobAutomation\Domain\IsJobDue;
use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Publisher\RetryPublisherInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersToNotifyQueryInterface;

final class PushScheduledJobsToQueueHandler implements PushScheduledJobsToQueueHandlerInterface
{
    public function __construct(
        private FindUsersToNotifyQueryInterface $findUsersToNotifyQuery,
        private RetryPublisherInterface $retryPublisher,
    ) {
    }

    public function handle(PushScheduledJobsToQueueQuery $query): void
    {
        $dueJobInstances = $this->getDueJobs($query->getScheduledJobInstances());

        foreach ($dueJobInstances as $dueJobInstance) {
            $this->retryPublisher->publish($dueJobInstance);
        }
    }

    /**
     * @return DueJobInstance[]
     */
    private function getDueJobs(array $scheduledJobs): array
    {
        $dueJobs = [];

        foreach ($scheduledJobs as $scheduledJob) {
            if (IsJobDue::fromScheduledJobInstance($scheduledJob, CronExpressionFactory::fromExpression($scheduledJob->cronExpression))) {
                $dueJobs[] = new DueJobInstance($scheduledJob, $this->getUsersToNotify($scheduledJob));
            }
        }

        return $dueJobs;
    }

    private function getUsersToNotify(ScheduledJobInstance $dueJobInstance): UserToNotifyCollection
    {
        return $this->findUsersToNotifyQuery->byUserIdsAndUserGroupsIds(
            $dueJobInstance->notifiedUsers,
            $dueJobInstance->notifiedUserGroups,
        );
    }
}
