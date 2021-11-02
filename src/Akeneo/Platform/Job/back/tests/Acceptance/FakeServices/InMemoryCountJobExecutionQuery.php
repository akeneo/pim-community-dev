<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Domain\Query\CountJobExecutionQueryInterface;

class InMemoryCountJobExecutionQuery implements CountJobExecutionQueryInterface
{
    private InMemoryJobExecutionRepository $jobExecutionRepository;

    public function __construct(InMemoryJobExecutionRepository $jobExecutionRepository)
    {
        $this->jobExecutionRepository = $jobExecutionRepository;
    }

    public function all(): int
    {
        return count($this->jobExecutionRepository->all());
    }
}
