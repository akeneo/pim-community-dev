<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Job;

use Akeneo\Platform\Bundle\ImportExportBundle\Purge\PurgeJobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeJobExecutions implements TaskletInterface
{
    protected StepExecution $stepExecution;

    public function __construct(private PurgeJobExecution $purgeJobExecution, private Logger $logger)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $days = (int) $this->stepExecution->getJobParameters()->get('days');

        if (0 === $days) {
            $this->purgeJobExecution->all();
            $this->logger->info('All jobs execution deleted');
        } else {
            $numberOfDeletedJobExecutions = $this->purgeJobExecution->olderThanDays($days);
            $this->logger->info(sprintf('Purged jobs execution older than %d days', $days));
            $this->logger->info(sprintf('%d jobs execution deleted', $numberOfDeletedJobExecutions));
        }
    }
}
