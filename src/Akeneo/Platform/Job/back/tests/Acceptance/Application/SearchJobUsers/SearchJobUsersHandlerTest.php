<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\SearchJobUsers;

use Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersHandler;
use Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersQuery;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemorySearchJobUsers;

class SearchJobUsersHandlerTest extends AcceptanceTestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_job_users()
    {
        $expectedJobUsers = ['julia'];
        $this->getSearchJobUsers()->mockSearchResult($expectedJobUsers);

        $query = new SearchJobUsersQuery();
        $query->search = 'ju';

        $result = $this->getHandler()->search($query);

        $this->assertEquals($expectedJobUsers, $result);
    }

    private function getSearchJobUsers(): InMemorySearchJobUsers
    {
        return $this->get('Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersInterface');
    }

    private function getHandler(): SearchJobUsersHandler
    {
        return $this->get('Akeneo\Platform\Job\Application\SearchJobUsers\SearchJobUsersHandler');
    }
}
