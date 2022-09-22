<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Command;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionHandler;
use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Domain\Event\CouldNotLaunchAutomatedJobEvent;
use Akeneo\Platform\JobAutomation\Domain\IsJobDue;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersToNotifyQueryInterface;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class PushScheduledJobsToQueueCommandSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag                                    $jobAutomationFeatureFlag,
        FindScheduledJobInstancesQueryInterface        $findScheduledJobInstancesQuery,
        IsJobDue                                       $filterDueJobInstances,
        UpdateScheduledJobInstanceLastExecutionHandler $refreshScheduledJobInstancesHandler,
        PublishJobToQueue                              $publishJobToQueue,
        EventDispatcherInterface                       $eventDispatcher,
        FindUsersToNotifyQueryInterface                $findUsersToNotifyQuery,
        LoggerInterface                                $logger,
        ClockInterface $clock,
    ): void
    {
        $eventDispatcher->addSubscriber(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($jobAutomationFeatureFlag,
            $findScheduledJobInstancesQuery,
            $filterDueJobInstances,
            $refreshScheduledJobInstancesHandler,
            $publishJobToQueue,
            $eventDispatcher,
            $findUsersToNotifyQuery,
            $logger,
            $clock,
        );
    }

    public function it_early_returns_if_feature_flag_is_not_enabled(
        InputInterface $input,
        OutputInterface $output,
        FeatureFlag $jobAutomationFeatureFlag,
        EventDispatcherInterface $eventDispatcher,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(false);
        $eventDispatcher->addSubscriber(Argument::any())->shouldNotBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    public function it_pushes_scheduled_jobs_to_queue(
        InputInterface                          $input,
        OutputInterface                         $output,
        FeatureFlag                             $jobAutomationFeatureFlag,
        FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        IsJobDue                                $filterDueJobInstances,
        FindUsersToNotifyQueryInterface         $findUsersToNotifyQuery,
        PublishJobToQueue                       $publishJobToQueue,
    ): void
    {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1');
        $scheduledJobInstance2 = $this->createScheduledJobInstance('job2');
        $scheduledJobInstance3 = $this->createScheduledJobInstance('job3', [1], [2, 3]);

        $findScheduledJobInstancesQuery
            ->all()
            ->shouldBeCalled()
            ->willReturn([$scheduledJobInstance1, $scheduledJobInstance2, $scheduledJobInstance3]);

        $filterDueJobInstances
            ->fromScheduledJobInstances([$scheduledJobInstance1, $scheduledJobInstance2, $scheduledJobInstance3])
            ->shouldBeCalled()
            ->willReturn([$scheduledJobInstance1, $scheduledJobInstance3]);

        $emptyUsersToNotify = new UserToNotifyCollection([]);
        $findUsersToNotifyQuery->byUserIdsAndUserGroupsIds([], [])->willReturn($emptyUsersToNotify);

        $usersToNotify = new UserToNotifyCollection([
            new UserToNotify('admin', 'admin@akeneo.com'),
            new UserToNotify('julia', 'julia@akeneo.com'),
        ]);
        $findUsersToNotifyQuery->byUserIdsAndUserGroupsIds([1], [2, 3])->willReturn($usersToNotify);

        $publishJobToQueue
            ->publish(
                $scheduledJobInstance1->code,
                [
                    'is_user_authenticated' => true,
                    'users_to_notify' => [],
                ],
                false,
                'job_automated_job1',
                [],
            )
            ->shouldBeCalled();
        $publishJobToQueue
            ->publish(
                $scheduledJobInstance3->code,
                [
                    'is_user_authenticated' => true,
                    'users_to_notify' => [
                        'admin',
                        'julia',
                    ],
                ],
                false,
                'job_automated_job3',
                [
                    'admin@akeneo.com',
                    'julia@akeneo.com',
                ],
            )
            ->shouldBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    public function it_handles_invalid_job_exceptions_on_publish_and_notify(
        InputInterface                          $input,
        OutputInterface                         $output,
        FeatureFlag                             $jobAutomationFeatureFlag,
        FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        IsJobDue                                $filterDueJobInstances,
        FindUsersToNotifyQueryInterface         $findUsersToNotifyQuery,
        PublishJobToQueue                       $publishJobToQueue,
        EventDispatcherInterface                $eventDispatcher,
    ): void
    {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1', [1], [2, 3]);
        $scheduledJobInstance2 = $this->createScheduledJobInstance('job2');
        $findScheduledJobInstancesQuery->all()->shouldBeCalled()->willReturn([$scheduledJobInstance1, $scheduledJobInstance2]);
        $filterDueJobInstances
            ->fromScheduledJobInstances([$scheduledJobInstance1, $scheduledJobInstance2])
            ->shouldBeCalled()
            ->willReturn([$scheduledJobInstance1, $scheduledJobInstance2]);

        $usersToNotify = new UserToNotifyCollection([
            new UserToNotify('admin', 'admin@akeneo.com'),
            new UserToNotify('julia', 'julia@akeneo.com'),
        ]);

        $findUsersToNotifyQuery->byUserIdsAndUserGroupsIds([1], [2, 3])->willReturn($usersToNotify);
        $violations = new ConstraintViolationList([
            new ConstraintViolation('wrong', 'wrong', [], 'wrong', 'path', 'wrong'),
            new ConstraintViolation('wrong2', 'wrong2', [], 'wrong2', 'path2', 'wrong2'),
        ]);

        $publishJobToQueue
            ->publish(
                $scheduledJobInstance1->code,
                [
                    'is_user_authenticated' => true,
                    'users_to_notify' => [
                        'admin',
                        'julia'
                    ],
                ],
                false,
                'job_automated_job1',
                [
                    'admin@akeneo.com',
                    'julia@akeneo.com',
                ],
            )
            ->willThrow(new InvalidJobException('job1', 'dummy', $violations));

        $eventDispatcher->dispatch(CouldNotLaunchAutomatedJobEvent::dueToInvalidJobInstance($scheduledJobInstance1, ['wrong', 'wrong2'], $usersToNotify))->shouldBeCalled();
        $emptyUsersToNotify = new UserToNotifyCollection([]);
        $findUsersToNotifyQuery->byUserIdsAndUserGroupsIds([], [])->shouldBeCalled()->willReturn($emptyUsersToNotify);
        $publishJobToQueue
            ->publish(
                $scheduledJobInstance2->code,
                [
                    'is_user_authenticated' => true,
                    'users_to_notify' => [],
                ],
                false,
                'job_automated_job2',
                [],
            )
            ->shouldBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    public function it_handles_infrastructure_exceptions_on_publish_and_retry(
        InputInterface $input,
        OutputInterface $output,
        FeatureFlag $jobAutomationFeatureFlag,
        FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        FilterDueJobInstances $filterDueJobInstances,
        FindUsersToNotifyQueryInterface $findUsersToNotifyQuery,
        PublishJobToQueue $publishJobToQueue,
        EventDispatcherInterface $eventDispatcher,
        ClockInterface $clock,
        LoggerInterface $logger,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1', [1], [2, 3]);
        $findScheduledJobInstancesQuery->all()->shouldBeCalled()->willReturn([$scheduledJobInstance1]);
        $filterDueJobInstances
            ->fromScheduledJobInstances([$scheduledJobInstance1])
            ->shouldBeCalled()
            ->willReturn([$scheduledJobInstance1]);

        $usersToNotify = new UserToNotifyCollection([
            new UserToNotify('admin', 'admin@akeneo.com'),
            new UserToNotify('julia', 'julia@akeneo.com'),
        ]);
        $findUsersToNotifyQuery->byUserIdsAndUserGroupsIds([1], [2, 3])->willReturn($usersToNotify);

        $publishJobToQueue
            ->publish(
                $scheduledJobInstance1->code,
                [
                    'is_user_authenticated' => true,
                    'users_to_notify' => [
                        'admin',
                        'julia'
                    ],
                ],
                false,
                'job_automated_job1',
                [
                    'admin@akeneo.com',
                    'julia@akeneo.com',
                ],
            )
            ->willThrow(new \Exception('PubSub unavailable'));

        $clock->sleep(1000)->shouldBeCalledTimes(1);
        $eventDispatcher->dispatch(CouldNotLaunchAutomatedJobEvent::dueToInternalError($scheduledJobInstance1, $usersToNotify))->shouldNotBeCalled();
        $logger->error('Cannot launch scheduled job due to an infrastructure error', ['error_message' => 'PubSub unavailable'])->shouldNotBeCalled();

        $publishJobToQueue
            ->publish(
                $scheduledJobInstance1->code,
                [
                    'is_user_authenticated' => true,
                    'users_to_notify' => [
                        'admin',
                        'julia'
                    ],
                ],
                false,
                'job_automated_job1',
                [
                    'admin@akeneo.com',
                    'julia@akeneo.com',
                ],
            )
            ->willThrow(new \Exception('PubSub unavailable'));

        $eventDispatcher->dispatch(CouldNotLaunchAutomatedJobEvent::dueToInternalError($scheduledJobInstance1, $usersToNotify))->shouldBeCalled();
        $logger->error('Cannot launch scheduled job due to an infrastructure error', ['error_message' => 'PubSub unavailable'])->shouldBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    private function createScheduledJobInstance(string $code, array $notifiedUsers = [], array $notifiedUserGroups = []): ScheduledJobInstance {
        return new ScheduledJobInstance(
            $code,
            'dummy',
            'import',
            [],
            $notifiedUsers,
            $notifiedUserGroups,
            '* * * * *',
            new \DateTimeImmutable('2022-10-30 00:00'),
            null,
            sprintf('job_automated_%s', $code),
        );
    }
}
