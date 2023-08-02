<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobWithStepsInterface;
use Akeneo\Tool\Component\Batch\Job\PausableJobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Query\SqlUpdateJobExecutionStatus;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PauseJobOnSigtermSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<string> $jobsAllowedToPause
     */
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LoggerInterface $logger,
        private readonly SqlUpdateJobExecutionStatus $updateJobExecutionStatus,
        private readonly JobRegistry $jobRegistry,
        private readonly array $jobsAllowedToPause,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'onBeforeJobExecution',
        ];
    }

    public function onBeforeJobExecution(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());

        if (!$this->featureFlags->isEnabled('pause_jobs') || !$this->isJobPausable($job) || !$this->isJobAllowedToPause($job)) {
            return;
        }

        pcntl_signal(\SIGTERM, function () use ($jobExecution) {
            if (!$jobExecution->isRunning()) {
                return;
            }

            $this->logger->notice('Received SIGTERM signal in PauseJobOnSigtermSubscriber and pausing the job.', [
                'job_execution_id' => $jobExecution->getId(),
                'job_code' => $jobExecution->getJobInstance()->getCode(),
            ]);

            $this->updateJobExecutionStatus->updateByJobExecutionId($jobExecution->getId(), new BatchStatus(BatchStatus::PAUSING));
        });
    }

    private function isJobPausable(JobInterface $job): bool
    {
        if ($job instanceof PausableJobInterface) {
            return $job->isPausable();
        }

        return false;
    }

    private function isJobAllowedToPause(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->jobsAllowedToPause);
    }
}
