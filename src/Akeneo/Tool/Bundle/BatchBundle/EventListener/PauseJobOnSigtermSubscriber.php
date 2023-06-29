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
    public function __construct(
        private readonly FeatureFlags $featureFlags,
        private readonly LoggerInterface $logger,
        private readonly SqlUpdateJobExecutionStatus $updateJobExecutionStatus,
        private readonly JobRegistry $jobRegistry,
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
        if (!$this->featureFlags->isEnabled('pause_jobs')) {
            return;
        }

        pcntl_signal(\SIGTERM, function () use ($event) {
            $jobExecution = $event->getJobExecution();
            $this->logger->notice('Received SIGTERM signal in PauseJobOnSigtermSubscriber and pausing the job.', [
                'job_execution_id' => $jobExecution->getId()
            ]);

            if (!$jobExecution->isRunning() || !$this->isJobExecutionPausable($jobExecution)) {
                return;
            }

            $this->updateJobExecutionStatus->updateByJobExecutionId($jobExecution->getId(), new BatchStatus(BatchStatus::PAUSING));
        });
    }

    private function isJobExecutionPausable(JobExecution $jobExecution): bool
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());

        if ($job instanceof PausableJobInterface) {
            return $job->isPausable();
        }

        return false;
    }
}
