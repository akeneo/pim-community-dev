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
use Doctrine\ORM\PersistentCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetJobExecutionTracking
{
    private const TRACKING_STATUS_COMPLETED = 'COMPLETED';
    private const TRACKING_STATUS_NOT_STARTED = 'NOT_STARTED';
    private const TRACKING_STATUS_IN_PROGRESS = 'IN_PROGRESS';

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
        $jobExecutionTracking->status = $this->getMappedStatus($jobExecution->getStatus());
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
                $stepsExecutionTracking[] = $this->createStepExecutionTrackingNotStartedFromStep($step);
                continue;
            }

            $stepExecution = $stepExecutions[$stepExecutionIndex];
            $stepsExecutionTracking[] = $this->createStepExecutionTrackingFromStepAndStepExecution($step, $stepExecution);
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

    private function createStepExecutionTrackingNotStartedFromStep(StepInterface $step): StepExecutionTracking
    {
        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->name = $step->getName();
        $stepExecutionTracking->status = self::TRACKING_STATUS_NOT_STARTED;
        if ($step instanceof TrackableStepInterface && $step->isTrackable()) {
            $stepExecutionTracking->isTrackable = true;
        }

        return $stepExecutionTracking;
    }

    private function createStepExecutionTrackingFromStepAndStepExecution(StepInterface $step, StepExecution $stepExecution): StepExecutionTracking
    {
        $duration = $this->calculateDuration($stepExecution);

        $stepExecutionTracking = new StepExecutionTracking();
        $stepExecutionTracking->name = $step->getName();
        $stepExecutionTracking->status = $this->getMappedStatus($stepExecution->getStatus());
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

    private function getMappedStatus(BatchStatus $batchStatus): string
    {
        switch ($batchStatus->getValue()) {
            case BatchStatus::STOPPING:
            case BatchStatus::STOPPED:
            case BatchStatus::FAILED:
            case BatchStatus::ABANDONED:
            case BatchStatus::UNKNOWN:
            case BatchStatus::COMPLETED:
                return self::TRACKING_STATUS_COMPLETED;
            case BatchStatus::STARTING:
                return self::TRACKING_STATUS_NOT_STARTED;
            case BatchStatus::STARTED:
                return self::TRACKING_STATUS_IN_PROGRESS;
            default:
                throw new \RuntimeException(sprintf('Batch status "%s" unsupported', $batchStatus->getValue()));
        }
    }

    private function calculateDuration(StepExecution $stepExecution): int
    {
        $now = $this->clock->now();
        $status = $this->getMappedStatus($stepExecution->getStatus());
        if ($status === self::TRACKING_STATUS_NOT_STARTED) {
            return 0;
        }

        $duration = $now->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
        if ($stepExecution->getEndTime()) {
            $duration = $stepExecution->getEndTime()->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
        }

        return $duration;
    }
}
