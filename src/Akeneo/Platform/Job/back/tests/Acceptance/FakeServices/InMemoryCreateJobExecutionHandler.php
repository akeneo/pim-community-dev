<?php

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class InMemoryCreateJobExecutionHandler implements CreateJobExecutionHandlerInterface
{
    private int $lastId = 0;

    public function createFromBatchCode(string $batchCode, array $jobExecutionConfig, ?string $username): JobExecution
    {
        return $this->createFakeJobExecution();
    }

    public function createFromJobInstance(JobInstance $jobInstance, array $jobExecutionConfig, ?string $username): JobExecution
    {
        return $this->createFakeJobExecution();
    }

    public function getLastId(): int
    {
        return $this->lastId;
    }

    private function createFakeJobExecution(): JobExecution
    {
        return new FakeJobExecution(++$this->lastId);
    }
}
