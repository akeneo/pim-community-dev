<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Command;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\JobAutomation\Application\GetDueJobInstances\GetDueJobInstancesHandler;
use Akeneo\Platform\JobAutomation\Application\NotifyUsers\NotifyUsersInvalidJobInstanceHandler;
use Akeneo\Platform\JobAutomation\Application\UpdateScheduledJobInstanceLastExecution\UpdateScheduledJobInstanceLastExecutionHandler;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Tool\Component\BatchQueue\Exception\InvalidJobException;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PushScheduledJobsToQueueCommandSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $jobAutomationFeatureFlag,
        GetDueJobInstancesHandler $getDueJobInstancesHandler,
        UpdateScheduledJobInstanceLastExecutionHandler $refreshScheduledJobInstancesHandler,
        PublishJobToQueue $publishJobToQueue,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        NotifyUsersInvalidJobInstanceHandler $emailNotifyUsersHandler,
    ): void {
        $this->beConstructedWith($jobAutomationFeatureFlag,
            $getDueJobInstancesHandler,
            $refreshScheduledJobInstancesHandler,
            $publishJobToQueue,
            $validator,
            $eventDispatcher,
            $emailNotifyUsersHandler
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
        InputInterface $input,
        OutputInterface $output,
        FeatureFlag $jobAutomationFeatureFlag,
        GetDueJobInstancesHandler $getDueJobInstancesHandler,
        PublishJobToQueue $publishJobToQueue,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1');
        $scheduledJobInstance2 = $this->createScheduledJobInstance('job2');
        $getDueJobInstancesHandler->handle()->shouldBeCalled()->willReturn([$scheduledJobInstance1, $scheduledJobInstance2]);

        $publishJobToQueue->publish($scheduledJobInstance1->code, ['is_user_authenticated' => true], false,'job_automated_job1')->shouldBeCalled();
        $publishJobToQueue->publish($scheduledJobInstance2->code, ['is_user_authenticated' => true], false,'job_automated_job2')->shouldBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    public function it_doesnt_push_invalid_scheduled_jobs_to_queue(
        InputInterface $input,
        OutputInterface $output,
        FeatureFlag $jobAutomationFeatureFlag,
        GetDueJobInstancesHandler $getDueJobInstancesHandler,
        PublishJobToQueue $publishJobToQueue,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1');
        $scheduledJobInstance2 = $this->createScheduledJobInstance('job2', false);
        $getDueJobInstancesHandler->handle()->shouldBeCalled()->willReturn([$scheduledJobInstance1, $scheduledJobInstance2]);

        $publishJobToQueue->publish($scheduledJobInstance1->code, ['is_user_authenticated' => true], false,'job_automated_job1')->shouldBeCalled();
        $publishJobToQueue->publish($scheduledJobInstance2->code, ['is_user_authenticated' => true], false,'job_automated_job2')->shouldNotBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    public function it_handles_exceptions_on_publish(
        InputInterface $input,
        OutputInterface $output,
        FeatureFlag $jobAutomationFeatureFlag,
        GetDueJobInstancesHandler $getDueJobInstancesHandler,
        PublishJobToQueue $publishJobToQueue,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1');
        $scheduledJobInstance2 = $this->createScheduledJobInstance('job2');
        $getDueJobInstancesHandler->handle()->shouldBeCalled()->willReturn([$scheduledJobInstance1, $scheduledJobInstance2]);

        $publishJobToQueue->publish($scheduledJobInstance1->code, ['is_user_authenticated' => true], false,'job_automated_job1')->willThrow(InvalidJobException::class);
        $publishJobToQueue->publish($scheduledJobInstance2->code, ['is_user_authenticated' => true], false,'job_automated_job2')->shouldBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    private function createScheduledJobInstance(string $code, bool $isScheduled = true): ScheduledJobInstance {
        return new ScheduledJobInstance(
            $code,
            'dummy',
            'import',
            [],
            [],
            [],
            $isScheduled,
            '* * * * *',
            new \DateTimeImmutable('2022-10-30 00:00'),
            null,
            sprintf('job_automated_%s', $code),
        );
    }
}
