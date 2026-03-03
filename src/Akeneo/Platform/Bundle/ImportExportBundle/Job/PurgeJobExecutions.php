<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Job;

use Akeneo\Platform\Bundle\ImportExportBundle\Purge\PurgeJobExecution;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeJobExecutions implements TaskletInterface
{
    protected StepExecution $stepExecution;

    public function __construct(private PurgeJobExecution $purgeJobExecution, private LoggerInterface $logger)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $days = (int) $this->stepExecution->getJobParameters()->get('days');
        $jobExecutionStatus = null;
        if ($this->stepExecution->getJobParameters()->has('status')) {
            $jobExecutionStatus = new BatchStatus($this->stepExecution->getJobParameters()->get('status'));
        }

        if (0 === $days) {
            $numberOfDeletedJobExecutions = $this->purgeJobExecution->olderThanHours(1, [], $jobExecutionStatus);
            $this->logger->info('Purged jobs execution older than 1 hour');
        } else {
            $numberOfDeletedJobExecutions = $this->purgeJobExecution->olderThanDays($days, [], $jobExecutionStatus);
            $this->logger->info(sprintf('Purged jobs execution older than %d days', $days));
        }
        $this->logger->info(sprintf('%d jobs execution deleted', $numberOfDeletedJobExecutions));
    }
}
