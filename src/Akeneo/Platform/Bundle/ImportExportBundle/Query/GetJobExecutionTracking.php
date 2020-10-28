<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Model\StepExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetJobExecutionTracking
{
    /** @var JobRegistry */
    private $jobRegistry;

    /** @var JobExecutionRepository */
    private $jobExecutionRepository;

    /** @var JobExecutionManager */
    private $jobExecutionManager;

    /** @var ClockInterface */
    private $clock;

    public function __construct(
        JobRegistry $jobRegistry,
        JobExecutionRepository $jobExecutionRepository,
        JobExecutionManager $jobExecutionManager,
        ClockInterface $clock
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobExecutionRepository = $jobExecutionRepository;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->clock = $clock;
    }

    public function execute(int $jobExecutionId): JobExecutionTracking
    {
        $jobExecution = $this->jobExecutionRepository->find($jobExecutionId);
        if (!$jobExecution instanceof JobExecution) {
            throw new \RuntimeException('The JobExecutionTracking query expect to find an existing JobExecution');
        }

        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);
        $jobName = $jobExecution->getJobInstance()->getJobName();
        $stepExecutions = $jobExecution->getStepExecutions();

        /* What do we do if we have a UndefinedJobException ? */
        $job = $this->jobRegistry->get($jobName);

        $jobExecutionTracking = new JobExecutionTracking();
        $jobExecutionTracking->status = (string)$jobExecution->getStatus();
        $jobExecutionTracking->currentStep = count($jobExecution->getStepExecutions());
        $jobExecutionTracking->totalSteps = count($job->getSteps());
        $jobExecutionTracking->steps = $this->getStepExecutionTracking($job, $stepExecutions);

        return $jobExecutionTracking;
    }

    private function getStepExecutionTracking(JobInterface $job, Collection $stepExecutions): array
    {
        $stepsExecutionTracking = [];

        /** @TODO add JobWithStepsInterface to assert getSteps function exist */
        /** @var StepInterface $step */
        foreach ($job->getSteps() as $step) {
            $stepName = $step->getName();
            $stepExecutionIndex = $this->searchFirstMatchingStepExecutionIndex($stepExecutions, $stepName);
            if ($stepExecutionIndex === -1) {
                $stepsExecutionTracking[] = $this->createStepExecutionTrackingNotStartedFromStep($job, $step);
                continue;
            }

            $stepExecution = $stepExecutions[$stepExecutionIndex];
            $stepsExecutionTracking[] = $this->createStepExecutionTrackingFromStepAndStepExecution($job, $step, $stepExecution);
            unset($stepExecutions[$stepExecutionIndex]);
        }

        return $stepsExecutionTracking;
    }

    private function searchFirstMatchingStepExecutionIndex(Collection $stepExecutions, string $stepName): int
    {
        foreach ($stepExecutions as $stepExecutionIndex => $stepExecution) {
            if ($stepExecution->getStepName() === $stepName) {
                return $stepExecutionIndex;
            }
        }

        /** @TODO maybe use Exception */
        return -1;
    }

    private function createStepExecutionTrackingNotStartedFromStep(JobInterface $job, StepInterface $step): StepExecutionTracking
    {
        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->jobName = $job->getName();
        $stepExecutionTracking->stepName = $step->getName();
        $stepExecutionTracking->status = (string)(new BatchStatus(BatchStatus::STARTING));
        if ($step instanceof TrackableStepInterface && $step->isTrackable()) {
            $stepExecutionTracking->isTrackable = true;
        }

        return $stepExecutionTracking;
    }

    private function createStepExecutionTrackingFromStepAndStepExecution(JobInterface $job, StepInterface $step, StepExecution $stepExecution): StepExecutionTracking
    {
        $duration = $this->computeDuration($stepExecution);

        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->jobName = $job->getName();
        $stepExecutionTracking->stepName = $step->getName();
        $stepExecutionTracking->status = (string)$stepExecution->getStatus();
        $stepExecutionTracking->duration = $duration;
        $stepExecutionTracking->hasError = count($stepExecution->getFailureExceptions()) !== 0 || count($stepExecution->getErrors()) !== 0;
        $stepExecutionTracking->hasWarning = count($stepExecution->getWarnings()) !== 0;

        if ($step instanceof TrackableStepInterface && $step->isTrackable()) {
            $stepExecutionTracking->isTrackable = true;
            $stepExecutionTracking->processedItems = $stepExecution->getProcessedItems();
            $stepExecutionTracking->totalItems = $stepExecution->getTotalItems();
        }

        return $stepExecutionTracking;
    }

    private function computeDuration(StepExecution $stepExecution): int
    {
        $now = $this->clock->now();
        if ($stepExecution->getStatus()->getValue() === BatchStatus::STARTING) {
            return 0;
        }

        $duration = $now->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
        if ($stepExecution->getEndTime()) {
            $duration = $stepExecution->getEndTime()->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
        }

        return $duration;
    }
}
