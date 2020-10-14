<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Query;

use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Model\StepExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;

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
            throw new \Exception(); //@TODO create execution + test
        }

        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);
        $jobName = $jobExecution->getJobInstance()->getJobName();

        /* What do we do if we have a UndefinedJobException ? */
        $job = $this->jobRegistry->get($jobName);

        $stepExecutions = $jobExecution->getStepExecutions();

        $jobExecutionTracking = new JobExecutionTracking();
        $jobExecutionTracking->status = $this->getMappedStatus($jobExecution->getStatus());
        $jobExecutionTracking->currentStep = count($jobExecution->getStepExecutions());
        $jobExecutionTracking->totalSteps = count($job->getSteps());

        $stepsExecutionTracking = [];
        foreach ($stepExecutions as $stepExecution) {
            $duration = $this->calculateDuration($stepExecution);

            $stepExecutionTracking = new StepExecutionTracking();
            $stepExecutionTracking->name = $stepExecution->getStepName();
            $stepExecutionTracking->status = $this->getMappedStatus($stepExecution->getStatus());
            $stepExecutionTracking->duration = $duration;
            $stepExecutionTracking->hasError = count($stepExecution->getErrors()) !== 0;
            $stepExecutionTracking->hasWarning = count($stepExecution->getWarnings()) !== 0;

            $step = $job->getStep($stepExecution->getStepName());
            if ($step instanceof TrackableStepInterface && $step->isTrackable()) {
                $stepExecutionTracking->isTrackable = true;
                $stepExecutionTracking->processedItems = $stepExecution->getProcessedItems();
                $stepExecutionTracking->totalItems = $stepExecution->getTotalItems();
            }

            $stepsExecutionTracking[] = $stepExecutionTracking;
        }

        $jobExecutionTracking->steps = $stepsExecutionTracking;

        return $jobExecutionTracking;
    }


    private function getMappedStatus(BatchStatus $batchStatus)
    {
        switch ($batchStatus->getValue()) {
            case BatchStatus::STOPPING:
            case BatchStatus::STOPPED:
            case BatchStatus::FAILED:
            case BatchStatus::ABANDONED:
            case BatchStatus::UNKNOWN:
            case BatchStatus::COMPLETED:
                return 'COMPLETED';
                break;
            case BatchStatus::STARTING:
                return 'NOT STARTED';
                break;
            case BatchStatus::STARTED:
                return 'IN PROGRESS';
                break;
            default:
                throw new \Exception('Not implemented');
        }
    }

    private function calculateDuration(StepExecution $stepExecution): int
    {
        $now = $this->clock->now();
        $status = $this->getMappedStatus($stepExecution->getStatus());
        if ($status === 'NOT STARTED') {
            return 0;
        }

        $duration = $now->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
        if ($stepExecution->getEndTime()) {
            $duration = $stepExecution->getEndTime()->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
        }

        return $duration;
    }
}
