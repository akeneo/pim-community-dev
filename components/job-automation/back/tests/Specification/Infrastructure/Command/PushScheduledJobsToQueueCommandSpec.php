<?php

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Command;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueHandlerInterface;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueQuery;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushScheduledJobsToQueueCommandSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag                              $jobAutomationFeatureFlag,
        FindScheduledJobInstancesQueryInterface  $findScheduledJobInstancesQuery,
        PushScheduledJobsToQueueHandlerInterface $pushScheduledJobsToQueueHandler,
    ): void {
        $this->beConstructedWith(
            $jobAutomationFeatureFlag,
            $findScheduledJobInstancesQuery,
            $pushScheduledJobsToQueueHandler,
        );
    }

    public function it_early_returns_if_feature_flag_is_not_enabled(
        InputInterface $input,
        OutputInterface $output,
        FeatureFlag $jobAutomationFeatureFlag,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(false);

        $this->run($input, $output)->shouldReturn(0);
    }

    public function it_pushes_scheduled_jobs_to_queue(
        InputInterface                           $input,
        OutputInterface                          $output,
        FeatureFlag                              $jobAutomationFeatureFlag,
        FindScheduledJobInstancesQueryInterface  $findScheduledJobInstancesQuery,
        PushScheduledJobsToQueueHandlerInterface $pushScheduledJobsToQueueHandler,
    ): void {
        $jobAutomationFeatureFlag->isEnabled()->shouldBeCalled()->willReturn(true);

        $scheduledJobInstance1 = $this->createScheduledJobInstance('job1');
        $scheduledJobInstance2 = $this->createScheduledJobInstance('job2');
        $scheduledJobInstance3 = $this->createScheduledJobInstance('job3', [1], [2, 3]);

        $findScheduledJobInstancesQuery
            ->all()
            ->shouldBeCalled()
            ->willReturn([$scheduledJobInstance1, $scheduledJobInstance2, $scheduledJobInstance3]);

        $pushScheduledJobsToQueueHandler
            ->handle(new PushScheduledJobsToQueueQuery([$scheduledJobInstance1, $scheduledJobInstance2, $scheduledJobInstance3]))
            ->shouldBeCalled();

        $this->run($input, $output)->shouldReturn(0);
    }

    private function createScheduledJobInstance(string $code, array $notifiedUsers = [], array $notifiedUserGroups = []): ScheduledJobInstance
    {
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
