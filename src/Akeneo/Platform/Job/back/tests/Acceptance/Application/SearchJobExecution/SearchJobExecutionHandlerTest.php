<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTable;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemorySearchJobExecution;

class SearchJobExecutionHandlerTest extends AcceptanceTestCase
{
    private InMemorySearchJobExecution $searchJobExecution;
    private SearchJobExecutionHandler $handler;

    protected function setUp(): void
    {
        $this->searchJobExecution = $this->get(SearchJobExecutionInterface::class);
        $this->handler = $this->get(SearchJobExecutionHandler::class);
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_job_execution_table_when_no_result()
    {
        $query = new SearchJobExecutionQuery();
        $result = $this->handler->search($query);
        $expectedResult = new JobExecutionTable([], 0);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_returns_a_job_execution_table()
    {
        $jobExecutionRows = [
            new JobExecutionRow(
                1,
                'first_job',
                'export',
                new \DateTimeImmutable('2020-01-02T00:00:00+00:00'),
                'admin',
                Status::fromLabel('COMPLETED'),
                false,
                new JobExecutionTracking(1, 2, []),
            ),
            new JobExecutionRow(
                2,
                'second_job',
                'export',
                new \DateTimeImmutable('2020-01-03T00:00:00+00:00'),
                'admin',
                Status::fromLabel('FAILED'),
                true,
                new JobExecutionTracking(1, 2, []),
            ),
        ];

        $this->searchJobExecution->mockSearchResult($jobExecutionRows);

        $query = new SearchJobExecutionQuery();
        $query->page = 1;

        $result = $this->handler->search($query);
        $expectedResult = new JobExecutionTable($jobExecutionRows, 2);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_when_sort_column_is_not_supported()
    {
        $query = new SearchJobExecutionQuery();
        $query->sortColumn = 'invalid_column';

        $this->expectExceptionObject(new \InvalidArgumentException('Sort column "invalid_column" is not supported'));

        $this->handler->search($query);
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_when_sort_direction_is_not_supported()
    {
        $query = new SearchJobExecutionQuery();
        $query->sortDirection = 'DASC';

        $this->expectExceptionObject(new \InvalidArgumentException('Sort direction "DASC" is not supported'));

        $this->handler->search($query);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_page_is_greater_than_50_and_no_filter(): void
    {
        $query = new SearchJobExecutionQuery();
        $query->page = 51;

        $this->expectExceptionObject(new \InvalidArgumentException('Page can not be greater than 50'));

        $this->handler->search($query);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_page_is_greater_than_50_and_at_least_one_filter_is_set(): void
    {
        $query = new SearchJobExecutionQuery();
        $query->type = ['export'];
        $query->page = 51;

        $this->expectExceptionObject(new \InvalidArgumentException('Page can not be greater than 50'));

        $this->handler->search($query);
    }
}
