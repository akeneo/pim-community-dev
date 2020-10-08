<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Step\StepInterface;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class JobExecutionProgressController
{
    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /**
     * @var JobExecutionManager
     */
    private $jobExecutionManager;

    /**
     * @var JobRegistry
     */
    private $jobRegistry;

    public function __construct(
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo,
        JobRegistry $jobRegistry
    ) {
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * Get jobs
     *
     * @param $identifier
     *
     * @return JsonResponse
     */
    public function getAction($jobId)
    {
        $jobExecution = $this->jobExecutionRepo->find($jobId);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }
        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);

        /* What do we do if we have a UndefinedJobException ? */
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());

        $stepExecutions = $jobExecution->getStepExecutions();
        $normalizedSteps = [];
        foreach ($stepExecutions as $stepExecution) {
            $now = new \Datetime('now');
            $duration = $now->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
            if ($stepExecution->getEndTime()) {
                $duration = $stepExecution->getEndTime()->getTimestamp() - $stepExecution->getStartTime()->getTimestamp();
            }

            $normalizedStep = [
                'title' => $stepExecution->getStepName(),
                'status' => $this->getMappedStatus($stepExecution->getStatus()),
                'has_warning' => count($stepExecution->getWarnings()) !== 0,
                'has_error' => count($stepExecution->getErrors()) !== 0,
                'duration' => $duration,
            ];

            $step = $job->getStep($stepExecution->getStepName());
            if ($step instanceof TrackableStepInterface && $step->isTrackable()) {
                $normalizedStep['item_processed'] = $stepExecution->getProcessCount();
                $normalizedStep['total_item'] = $stepExecution->getTotalItems();
            }

            $normalizedSteps[] = $normalizedStep;
        }

        $response = [
            'status' => $jobExecution->getStatus()->getValue(),
            'currentStep' => count($jobExecution->getStepExecutions()),
            'totalSteps' => count($job->getSteps()),
            'steps' => $normalizedSteps
        ];

        return new JsonResponse($response);
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
}
