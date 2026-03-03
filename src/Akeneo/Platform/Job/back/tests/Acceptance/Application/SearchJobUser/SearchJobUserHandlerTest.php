<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\SearchJobUser;

use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserHandler;
use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserInterface;
use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserQuery;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemorySearchJobUser;

class SearchJobUserHandlerTest extends AcceptanceTestCase
{
    private InMemorySearchJobUser $searchJobUser;
    private SearchJobUserHandler $handler;

    protected function setUp(): void
    {
        $this->searchJobUser = $this->get(SearchJobUserInterface::class);
        $this->handler = $this->get(SearchJobUserHandler::class);
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_job_users()
    {
        $expectedJobUsers = ['julia'];
        $this->searchJobUser->mockSearchResult($expectedJobUsers);

        $query = new SearchJobUserQuery();
        $query->search = 'ju';

        $result = $this->handler->search($query);

        $this->assertEquals($expectedJobUsers, $result);
    }
}
