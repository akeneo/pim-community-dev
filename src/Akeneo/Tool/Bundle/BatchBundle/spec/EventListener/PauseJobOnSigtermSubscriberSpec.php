<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Query\SqlUpdateJobExecutionStatus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class PauseJobOnSigtermSubscriberSpec extends ObjectBehavior
{
    function let(
        FeatureFlags $featureFlags,
        LoggerInterface $logger,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        SqlUpdateJobExecutionStatus $updateJobExecutionStatus
    ) {
        $this->beConstructedWith($featureFlags, $logger, $updateJobExecutionStatus);
        $jobExecution->getId()->willReturn(10);
        $event->getJobExecution()->willReturn($jobExecution);
    }

    function it_log_a_message_when_pause_jobs_is_enabled_and_sigterm_is_received(
        FeatureFlags $featureFlags,
        LoggerInterface $logger,
        JobExecutionEvent $event,
    ) {
        $featureFlags->isEnabled('pause_jobs')->willReturn(true);
        $logger->info('Received SIGTERM signal.', ['job_execution_id' => 10])->shouldBeCalled();
        $this->onBeforeJobExecution($event);

        posix_kill(posix_getpid(), SIGTERM);
    }

    function it_does_nothing_when_pause_jobs_is_not_enabled_and_sigterm_is_received(
        FeatureFlags $featureFlags,
        LoggerInterface $logger,
        JobExecutionEvent $event,
    ) {
        $featureFlags->isEnabled('pause_jobs')->willReturn(false);
        $logger->info('Received SIGTERM signal.', ['job_execution_id' => 10])->shouldNotBeCalled();
        $this->onBeforeJobExecution($event);

        posix_kill(posix_getpid(), SIGTERM);
    }

    function it_updates_status_when_pause_jobs_is_enabled_and_sigterm_is_received(
        FeatureFlags $featureFlags,
        JobExecutionEvent $event,
        JobExecution $jobExecution,
        SqlUpdateJobExecutionStatus $updateJobExecutionStatus,

    ) {
        $featureFlags->isEnabled('pause_jobs')->willReturn(true);
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getId()->willReturn(1234);
        $updateJobExecutionStatus->updateByJobExecutionId(1234, Argument::any())->shouldBeCalled();
        $this->onBeforeJobExecution($event);

        posix_kill(posix_getpid(), SIGTERM);
    }
}
