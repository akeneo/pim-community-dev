<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue;

use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
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
        private PublishJobToQueueInterface $publishJobToQueue,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(PushScheduledJobsToQueueQuery $query): void
    {
        foreach ($query->getDueJobInstance() as $dueJobInstance) {
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
            } catch (Exception $exception) {
                // TODO move $usersToNotify to $dueJobInstance
                $this->eventDispatcher->dispatch(
                    CouldNotLaunchAutomatedJobEvent::dueToInternalError($dueJobInstance, $usersToNotify),
                );

                $this->logger->error('Cannot launch automated job', [
                    'error_message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
