<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionTable;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SearchJobExecutionHandlerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_the_search_result()
    {
        $query = new SearchJobExecutionQuery();
        $result = $this->getHandler()->search($query);
        $expectedResult = new JobExecutionTable([], 0, 0);

        $this->assertEquals($expectedResult, $result);
    }

    private function getHandler(): SearchJobExecutionHandler
    {
        return static::$container->get('Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionHandler');
    }
}
