<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionTable;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemorySearchJobExecution;

class SearchJobExecutionHandlerTest extends AcceptanceTestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_job_execution_table_when_no_result()
    {
        $query = new SearchJobExecutionQuery();
        $result = $this->getHandler()->search($query);
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
                'COMPLETED',
                3,
                0,
                1,
                2,
                false
            ),
            new JobExecutionRow(
                2,
                'second_job',
                'export',
                new \DateTimeImmutable('2020-01-03T00:00:00+00:00'),
                'admin',
                'FAILED',
                4,
                1,
                1,
                2,
                true
            ),
        ];

        $this->getSearchJobExecution()->mockSearchResult($jobExecutionRows);

        $query = new SearchJobExecutionQuery();
        $query->page = 1;

        $result = $this->getHandler()->search($query);
        $expectedResult = new JobExecutionTable($jobExecutionRows, 2);

        $this->assertEquals($expectedResult, $result);
    }

    private function getSearchJobExecution(): InMemorySearchJobExecution
    {
        return $this->get('Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface');
    }

    private function getHandler(): SearchJobExecutionHandler
    {
        return $this->get('Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler');
    }
}
