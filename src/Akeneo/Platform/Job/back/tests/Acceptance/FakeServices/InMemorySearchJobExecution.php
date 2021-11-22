<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use \Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;

class InMemorySearchJobExecution implements SearchJobExecutionInterface
{
    private array $jobExecutionRows = [];

    /**
     * @param JobExecutionRow[] $jobExecutionRows
     */
    public function mockSearchResult(array $jobExecutionRows): void
    {
        $this->jobExecutionRows = $jobExecutionRows;
    }

    public function search(SearchJobExecutionQuery $query): array
    {
        return $this->jobExecutionRows;
    }

    public function count(SearchJobExecutionQuery $query): int
    {
        return count($this->jobExecutionRows);
    }
}
