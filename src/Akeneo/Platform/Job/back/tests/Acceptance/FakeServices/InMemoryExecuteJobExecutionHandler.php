<?php

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;

class InMemoryExecuteJobExecutionHandler implements ExecuteJobExecutionHandlerInterface
{
    public function executeFromJobExecutionId(int $executionId): JobExecution
    {
        return new FakeJobExecution($executionId);
    }
}
