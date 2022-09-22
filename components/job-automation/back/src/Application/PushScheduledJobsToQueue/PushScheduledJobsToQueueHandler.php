<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue;

use Akeneo\Platform\JobAutomation\Domain\CronExpressionFactory;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\IsJobDue;
use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersToNotifyQueryInterface;
use Akeneo\Tool\Component\BatchQueue\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class PushScheduledJobsToQueueHandler implements PushScheduledJObsToQueueHandlerInterface
{
    public function __construct(
        private FindUsersToNotifyQueryInterface $findUsersToNotifyQuery,
        private PublishJobToQueueInterface      $publishJobToQueue,
        private EventDispatcherInterface        $eventDispatcher,
        private LoggerInterface                 $logger,
    ) {
    }

    public function handle(PushScheduledJobsToQueueQuery $query): void
    {
        $dueJobInstances = $this->getDueJobs($query->getScheduledJobInstances());

        if (empty($dueJobInstances)) {
            return;
        }

        foreach ($dueJobInstances as $dueJobInstance) {

            try {
                $this->publishJobToQueue->publish(
                    jobInstanceCode: $dueJobInstance->getScheduledJobInstance()->code,
                    config: [
                        'is_user_authenticated' => true,
                        'users_to_notify' => $dueJobInstance->getUsersToNotify()->getUsernames(),
                    ],
                    username: $dueJobInstance->getScheduledJobInstance()->runningUsername,
                    emails: $dueJobInstance->getUsersToNotify()->getUniqueEmails(),
                );
            } catch (InvalidJobException $exception) {
                $errorMessages = array_map(
                    static fn (ConstraintViolationInterface $constraintViolation) => $constraintViolation->getMessage(),
                    iterator_to_array($exception->getViolations()),
                );

                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance($dueJobInstance, $errorMessages),
                );
            } catch (Exception $exception) {
                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInternalError($dueJobInstance),
                );

                $this->logger->error('Cannot launch automated job', [
                    'error_message' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return DueJobInstance[]
     */
    private function getDueJobs(array $scheduledJobs): array
    {
        $dueJobs = [];

        if (!empty($scheduledJobs)) {
            foreach ($scheduledJobs as $scheduledJob) {
                if (IsJobDue::fromScheduledJobInstances($scheduledJob, CronExpressionFactory::fromExpression($scheduledJob->cronExpression))) {
                    $dueJobs[] = new DueJobInstance($scheduledJob, $this->getUsersToNotify($scheduledJob));
                }
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
