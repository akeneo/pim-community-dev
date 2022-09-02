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
use Akeneo\Platform\JobAutomation\Application\GetDueJobInstances\GetDueJobInstancesHandler;
use Akeneo\Platform\JobAutomation\Application\NotifyUsers\NotifyUsersInvalidJobInstanceCommand;
use Akeneo\Platform\JobAutomation\Application\NotifyUsers\NotifyUsersInvalidJobInstanceHandler;
use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionHandler;
use Akeneo\Platform\JobAutomation\Infrastructure\EventSubscriber\RefreshScheduledJobInstanceAfterJobPublished;
use Akeneo\Tool\Component\BatchQueue\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class PushScheduledJobsToQueueCommand extends Command
{
    public static $defaultName = 'pim:job-automation:push-scheduled-jobs-to-queue';

    public function __construct(
        private FeatureFlag $jobAutomationFeatureFlag,
        private GetDueJobInstancesHandler $getDueJobInstancesHandler,
        private UpdateScheduledJobInstanceLastExecutionHandler $updateScheduledJobInstanceLastExecutionHandler,
        private PublishJobToQueue $publishJobToQueue,
        private EventDispatcherInterface $eventDispatcher,
        private NotifyUsersInvalidJobInstanceHandler $emailNotifyUsersHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->jobAutomationFeatureFlag->isEnabled()) {
            return 0;
        }

        $this->eventDispatcher->addSubscriber(new RefreshScheduledJobInstanceAfterJobPublished($this->updateScheduledJobInstanceLastExecutionHandler));

        $dueJobInstances = $this->getDueJobInstancesHandler->handle();

        foreach ($dueJobInstances as $dueJobInstance) {
            try {
                if (!$dueJobInstance->isScheduled) {
                    continue;
                }

                $this->publishJobToQueue->publish(
                    jobInstanceCode: $dueJobInstance->code,
                    config: [
                        'is_user_authenticated' => true,
                    ],
                    username: $dueJobInstance->runningUsername,
                );
            } catch (InvalidJobException|\Exception $exception) {
                $command = new NotifyUsersInvalidJobInstanceCommand(
                    $exception->getMessage(),
                    $dueJobInstance,
                    $dueJobInstance->notifiedUserGroups,
                    $dueJobInstance->notifiedUsers,
                );

                $this->emailNotifyUsersHandler->handle($command);

                continue;
            }
        }

        return 0;
    }
}
