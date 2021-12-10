<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\FindJobTypes\FindJobTypesInterface;

class InMemoryFindJobTypes implements FindJobTypesInterface
{
    private array $jobTypes = [];

    public function mockFindResult(array $jobTypes): void
    {
        $this->jobTypes = $jobTypes;
    }

    public function visible(): array
    {
        return $this->jobTypes;
    }
}
