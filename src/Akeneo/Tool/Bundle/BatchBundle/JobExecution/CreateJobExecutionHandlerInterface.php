<?php

namespace Akeneo\Tool\Bundle\BatchBundle\JobExecution;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

/**
 * Create and persist a new JobExecution for the provided job code
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
interface CreateJobExecutionHandlerInterface
{
    public function createFromBatchCode(string $batchCode, array $jobExecutionConfig, ?string $username): JobExecution;

    public function createFromJobInstance(JobInstance $jobInstance, array $jobExecutionConfig, ?string $username): JobExecution;
}
