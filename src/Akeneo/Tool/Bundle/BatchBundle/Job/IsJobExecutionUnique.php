<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Job;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Query\SqlGetRunningJobExecution;
use Psr\Log\LoggerInterface;

/**
 * Dedicated service to check for a unique job execution, ie the only one executing for a job code
 *
 * @author    JMLeroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsJobExecutionUnique
{
    private const OUTDATED_JOB_EXECUTION_TIME = '-10 MINUTES';

    public function __construct(
        private SqlGetRunningJobExecution $getRunningJobExecution,
        private JobExecutionManager $executionManager,
        private JobExecutionRepository $jobExecutionRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $jobCode): bool
    {
        $runningExecutions = $this->getRunningJobExecution->getByJobCode($jobCode);

        foreach ($runningExecutions as $jobExecutionData) {
            // In case of an old job execution that has not been marked as failed.
            if (null !== $jobExecutionData['updated_time']
                && new \DateTime($jobExecutionData['updated_time']) < new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)
            ) {
                $this->logger->info(
                    'Job execution "{job_id}" is outdated: let\'s mark it has failed.',
                    ['message' => 'job_execution_outdated', 'job_id' => $jobExecutionData['id']]
                );
                $this->executionManager->markAsFailed(
                    $this->jobExecutionRepository->find((int)$jobExecutionData['id'])
                );
            } elseif (new \DateTime($jobExecutionData['start_time']) > new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)
            ) {
                return false;
            }
        }

        return true;
    }
}
