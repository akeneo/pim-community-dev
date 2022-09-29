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

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Publisher;

use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class RetryPublisherSpec extends ObjectBehavior
{
    public function let(
        ClockInterface $clock,
        EventDispatcher $eventDispatcher,
        LoggerInterface $logger,
        PublishJobToQueueInterface $publishJobToQueue
    ) {
        $this->beConstructedWith(
            $clock,
            $eventDispatcher,
            $logger,
            $publishJobToQueue
        );
    }

    public function it_push_job_without_error(
        PublishJobToQueueInterface $publishJobToQueue
    ) {
        $dueJobInstance = $this->getDueJobInstance();

        $publishJobToQueue->publish(
            jobInstanceCode: 'scheduled_job_instance',
            config: [
                'is_user_authenticated' => true,
                'users_to_notify' => [],
            ],
            noLog: false,
            username: 'job_automated_scheduled_job_instance',
            emails: [],
        )->shouldBecalled();

        $this->publish($dueJobInstance);
    }

    public function it_dispatch_an_event_on_invalid_job(
        PublishJobToQueueInterface $publishJobToQueue,
        EventDispatcher $eventDispatcher,
    ) {
        $publishJobToQueue->publish(
            "scheduled_job_instance",
            ["is_user_authenticated" => true, "users_to_notify" => []],
            false,
            "job_automated_scheduled_job_instance",
            []
        )->willThrow(
            new InvalidJobException(
                'scheduled_job_instance',
                'scheduled job instance',
                new ConstraintViolationList([new ConstraintViolation(
                    message: "some validation error",
                    messageTemplate: null,
                    parameters: [],
                    root: null,
                    propertyPath: null,
                    invalidValue: null
                )])
            )
        );

        $dueJobInstance = $this->getDueJobInstance();

        $eventDispatcher->dispatch(CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance($dueJobInstance, ['some validation error']))->shouldBeCalled();

        $this->publish($dueJobInstance);
    }

    public function it_retry_once_then_dispatch_an_event_on_exception(
        PublishJobToQueueInterface $publishJobToQueue,
        EventDispatcher $eventDispatcher,
        LoggerInterface $logger,
        ClockInterface $clock
    ) {
        $exception = new \Exception("system error");

        $publishJobToQueue->publish(
            "scheduled_job_instance",
            ["is_user_authenticated" => true, "users_to_notify" => []],
            false,
            "job_automated_scheduled_job_instance",
            []
        )->willThrow(
            $exception
        );

        $dueJobInstance = $this->getDueJobInstance();

        $clock->sleep(1000)->shouldBeCalled();

        $eventDispatcher->dispatch(CouldNotLaunchAutomatedJobEvent::dueToInternalError($dueJobInstance))->shouldBeCalled();

        $logger->error('Cannot launch scheduled job due to an infrastructure error', [
            'error_message' => $exception->getMessage(),
        ])->shouldBeCalled();

        $this->publish($dueJobInstance);
    }

    private function getDueJobInstance()
    {
        $scheduledJobInstance = new ScheduledJobInstance(
            code: 'scheduled_job_instance',
            label: 'scheduled job instance',
            type: 'import',
            rawParameters: [],
            notifiedUsers: [],
            notifiedUserGroups: [],
            cronExpression: '0 */4 * * *',
            setupDate: new \DateTimeImmutable('2022-10-30 00:00'),
            lastExecutionDate: null,
            runningUsername: 'job_automated_scheduled_job_instance'
        );
        
        $userToNotify = new UserToNotifyCollection([]);

        return new DueJobInstance($scheduledJobInstance, $userToNotify);
    }
}
