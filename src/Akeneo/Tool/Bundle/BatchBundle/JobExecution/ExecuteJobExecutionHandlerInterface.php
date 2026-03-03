<?php

namespace Akeneo\Tool\Bundle\BatchBundle\JobExecution;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

/**
 * Execute a JobExecution with the provided ID
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
interface ExecuteJobExecutionHandlerInterface
{
    public function executeFromJobExecutionId(int $executionId): JobExecution;
}
