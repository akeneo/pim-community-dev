<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

class InMemoryJobExecutionRepository
{
    private array $normalizedJobExecution = [];

    public function addJobExecution(array $jobExecution): void
    {
        $this->normalizedJobExecution[] = $jobExecution;
    }

    public function all(): array
    {
        return $this->normalizedJobExecution;
    }
}
