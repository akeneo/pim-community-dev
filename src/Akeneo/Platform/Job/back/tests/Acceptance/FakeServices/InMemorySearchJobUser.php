<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserInterface;
use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserQuery;

class InMemorySearchJobUser implements SearchJobUserInterface
{
    private array $jobUsers = [];

    public function mockSearchResult(array $jobUsers): void
    {
        $this->jobUsers = $jobUsers;
    }

    public function search(SearchJobUserQuery $query): array
    {
        return $this->jobUsers;
    }
}
