<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersInterface;
use Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersQuery;

class InMemorySearchJobUsers implements SearchJobUsersInterface
{
    private array $jobUsers = [];

    public function mockSearchResult(array $jobUsers): void
    {
        $this->jobUsers = $jobUsers;
    }

    public function search(SearchJobUsersQuery $query): array
    {
        return $this->jobUsers;
    }
}
