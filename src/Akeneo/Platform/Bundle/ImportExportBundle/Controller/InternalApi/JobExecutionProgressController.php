<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Step\TrackableStepInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class JobExecutionProgressController
{
    /**
     * @var JobExecutionRepository
     */
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

    public function getAction(string $jobId): JsonResponse
    {
        $jobExecution = $this->jobExecutionRepo->find($jobId);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }
        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);
        $jobName = $jobExecution->getJobInstance()->getJobName();

        /* What do we do if we have a UndefinedJobException ? */
        $job = $this->jobRegistry->get($jobName);

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
                $normalizedStep['processed_item'] = $stepExecution->getProcessedItem();
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
}
