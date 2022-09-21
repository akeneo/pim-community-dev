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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Command;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJObsToQueueHandlerInterface;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueQuery;
use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionHandlerInterface;
use Akeneo\Platform\JobAutomation\Domain\FilterDueJobInstances;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use Akeneo\Platform\JobAutomation\Infrastructure\EventSubscriber\RefreshScheduledJobInstanceAfterJobPublished;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class PushScheduledJobsToQueueCommand extends Command
{
    public static $defaultName = 'pim:job-automation:push-scheduled-jobs-to-queue';

    private const MAX_RETRY = 1;
    private const RETRY_DELAY_IN_MILLISECOND = 1000;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private FeatureFlag $jobAutomationFeatureFlag,
        private FilterDueJobInstances $filterDueJobInstances,
        private FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        private PushScheduledJObsToQueueHandlerInterface $pushScheduledJobsToQueueHandler,
        private UpdateScheduledJobInstanceLastExecutionHandlerInterface $updateScheduledJobInstanceLastExecutionHandler,
        private ClockInterface $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->jobAutomationFeatureFlag->isEnabled()) {
            return 0;
        }

        $this->eventDispatcher->addSubscriber(new RefreshScheduledJobInstanceAfterJobPublished($this->updateScheduledJobInstanceLastExecutionHandler));

        $scheduledJobInstances = $this->findScheduledJobInstancesQuery->all();
        $dueJobInstances = $this->filterDueJobInstances->fromScheduledJobInstances($scheduledJobInstances);

        foreach ($dueJobInstances as $dueJobInstance) {
            $this->pushJobToQueue($dueJobInstance);
        }

        return 0;
    }

    private function pushJobToQueue(ScheduledJobInstance $scheduledJobInstance): void
    {
        $usersToNotify = $this->findUsersToNotifyQuery->byUserIdsAndUserGroupsIds(
            $scheduledJobInstance->notifiedUsers,
            $scheduledJobInstance->notifiedUserGroups,
        );

        $shouldRetry = false;
        $retryCount = 0;

        do {
            try {
                $this->publishJobToQueue->publish(
                    jobInstanceCode: $scheduledJobInstance->code,
                    config: [
                        'is_user_authenticated' => true,
                        'users_to_notify' => $usersToNotify->getUsernames(),
                    ],
                    username: $scheduledJobInstance->runningUsername,
                    emails: $usersToNotify->getUniqueEmails(),
                );
            } catch (InvalidJobException $exception) {
                $errorMessages = array_map(
                    static fn (ConstraintViolationInterface $constraintViolation) => $constraintViolation->getMessage(),
                    iterator_to_array($exception->getViolations()),
                );

                // TODO move $usersToNotify to $scheduledJobInstance
                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance($scheduledJobInstance, $errorMessages, $usersToNotify),
                );
            } catch (\Exception $exception) {
                $shouldRetry = self::MAX_RETRY > $retryCount;
                if ($shouldRetry) {
                    $exponentialBackoff = self::RETRY_DELAY_IN_MILLISECOND * ($retryCount + 1);
                    $this->clock->sleep($exponentialBackoff);
                    ++$retryCount;
                    continue;
                }

                // TODO move $usersToNotify to $scheduledJobInstance
                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInternalError($scheduledJobInstance, $usersToNotify),
                );

                $this->logger->error('Cannot launch scheduled job due to an infrastructure error', [
                    'error_message' => $exception->getMessage(),
                ]);
            }
        } while ($shouldRetry);

        $this->pushScheduledJobsToQueueHandler->handle(
            new PushScheduledJobsToQueueQuery($this->findScheduledJobInstancesQuery->all())
        );

        return 0;
    }
}
