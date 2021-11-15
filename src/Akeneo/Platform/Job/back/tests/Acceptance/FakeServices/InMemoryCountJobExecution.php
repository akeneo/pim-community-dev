<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Domain\Query\CountJobExecutionInterface;

class InMemoryCountJobExecution implements CountJobExecutionInterface
{
    private int $jobExecutionCount = 0;

    public function mockResult(int $jobExecutionCount): void
    {
        $this->jobExecutionCount = $jobExecutionCount;
    }

    public function all(): int
    {
        return $this->jobExecutionCount;
    }
}
