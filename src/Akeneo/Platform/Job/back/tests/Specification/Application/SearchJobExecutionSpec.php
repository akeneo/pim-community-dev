<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\Application\SearchJobExecution;
use Akeneo\Platform\Job\Application\SearchJobExecutionTableResult;
use Akeneo\Platform\Job\Domain\Query\CountJobExecutionQueryInterface;
use PhpSpec\ObjectBehavior;

class SearchJobExecutionSpec extends ObjectBehavior
{
    public function let(CountJobExecutionQueryInterface $countJobExecutionQuery)
    {
        $this->beConstructedWith($countJobExecutionQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SearchJobExecution::class);
    }

    public function it_returns_search_job_result(CountJobExecutionQueryInterface $countJobExecutionQuery)
    {
        $countJobExecutionQuery->all()->willReturn(10);
        $expectedSearchJobExecutionTableResult = new SearchJobExecutionTableResult([], 10, 10);
        $this->search()->shouldBeLike($expectedSearchJobExecutionTableResult);
    }
}
