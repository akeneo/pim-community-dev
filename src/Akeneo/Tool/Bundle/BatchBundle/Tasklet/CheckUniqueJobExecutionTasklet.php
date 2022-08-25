<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Tasklet;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Query\SqlGetRunningJobExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * Simple task to check that only one instance of a job is running.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckUniqueJobExecutionTasklet implements TaskletInterface
{
    private const OUTDATED_JOB_EXECUTION_TIME = '-10 MINUTES';

    private StepExecution $stepExecution;

    public function __construct(
        private string $jobCode,
        private SqlGetRunningJobExecution $getRunningJobExecution,
        private JobExecutionManager $executionManager,
        private JobExecutionRepository $jobExecutionRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        if (!$this->isJobUniqueExecution()) {
            throw new JobInterruptedException(
                sprintf('Another instance of job %s is already running.', $this->jobCode),
            );
        }
    }

    private function isJobUniqueExecution(): bool
    {
        $jobExecutionData = $this->getRunningJobExecution->getByJobCode($this->jobCode);

        if (null === $jobExecutionData) {
            return true;
        }

        // In case of an old job execution that has not been marked as failed.
        if (null !== $jobExecutionData['updated_time']
            && new \DateTime($jobExecutionData['updated_time']) < new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)
        ) {
            $this->logger->info(
                'Job execution "{job_id}" is outdated: let\'s mark it has failed.',
                ['message' => 'job_execution_outdated', 'job_id' => $jobExecutionData['id']]
            );
            $this->executionManager->markAsFailed($this->jobExecutionRepository->find((int)$jobExecutionData['id']));

            return true;
        }

        return false;
    }
}
