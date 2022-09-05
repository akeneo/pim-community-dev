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
use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionHandler;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\FilterDueJobInstances;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersToNotifyQueryInterface;
use Akeneo\Platform\JobAutomation\Infrastructure\EventSubscriber\RefreshScheduledJobInstanceAfterJobPublished;
use Akeneo\Tool\Component\BatchQueue\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class PushScheduledJobsToQueueCommand extends Command
{
    public static $defaultName = 'pim:job-automation:push-scheduled-jobs-to-queue';

    public function __construct(
        private FeatureFlag $jobAutomationFeatureFlag,
        private FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        private FilterDueJobInstances $filterDueJobInstances,
        private UpdateScheduledJobInstanceLastExecutionHandler $updateScheduledJobInstanceLastExecutionHandler,
        private PublishJobToQueue $publishJobToQueue,
        private EventDispatcherInterface $eventDispatcher,
        private FindUsersToNotifyQueryInterface $findUsersToNotifyQuery,
        private LoggerInterface $logger,
        private NotifyUsersInvalidJobInstanceHandler $pimNotifNotifyUsersHandler,
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
            $usersToNotify = $this->findUsersToNotifyQuery->byUserIdsAndUserGroupsIds(
                $dueJobInstance->notifiedUsers,
                $dueJobInstance->notifiedUserGroups,
            );

            try {
                $this->publishJobToQueue->publish(
                    jobInstanceCode: $dueJobInstance->code,
                    config: [
                        'is_user_authenticated' => true,
                        'users_to_notify' => $usersToNotify->getUsernames(),
                    ],
                    username: $dueJobInstance->runningUsername,
                    emails: $usersToNotify->getUniqueEmails(),
                );
            } catch (InvalidJobException $exception) {
                $errorMessages = array_map(
                    static fn (ConstraintViolationInterface $constraintViolation) => $constraintViolation->getMessage(),
                    iterator_to_array($exception->getViolations()),
                );

                // TODO move $usersToNotify to $dueJobInstance
                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance($dueJobInstance, $errorMessages, $usersToNotify),
                );
            } catch (\Exception $exception) {
                // TODO move $usersToNotify to $dueJobInstance
                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInternalError($dueJobInstance, $usersToNotify),
                );
                $this->pimNotifNotifyUsersHandler->handle($command);

                $this->logger->error('Cannot launch automated job', [
                    'error_message' => $exception->getMessage(),
                ]);
            }
        }

        return 0;
    }
}
