<?php

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\LaunchJobInstance\GenerateJobExecutionUrlInterface;

class InMemoryGenerateJobExecutionUrl implements GenerateJobExecutionUrlInterface
{
    public function fromJobExecutionId(int $jobExecutionId): string
    {
        return sprintf('/job/show/%d', $jobExecutionId);
    }
}
