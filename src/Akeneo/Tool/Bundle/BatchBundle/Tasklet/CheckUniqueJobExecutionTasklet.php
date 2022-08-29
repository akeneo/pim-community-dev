<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Tasklet;

use Akeneo\Tool\Bundle\BatchBundle\Job\IsJobExecutionUnique;
use Akeneo\Tool\Component\Batch\Job\JobInterruptedException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Simple task to check that only one execution of a job is running.
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckUniqueJobExecutionTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private string $jobCode,
        private IsJobExecutionUnique $isJobExecutionUnique,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        if (!($this->isJobExecutionUnique)($this->jobCode)) {
            throw new JobInterruptedException(
                sprintf('Another instance of job %s is already running.', $this->jobCode),
            );
        }
    }
}
