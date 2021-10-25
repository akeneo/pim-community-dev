<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\Application\SearchJob;
use Akeneo\Platform\Job\Domain\Model\SearchJobResult;
use Akeneo\Platform\Job\Domain\Query\CountJobQueryInterface;
use PhpSpec\ObjectBehavior;

class SearchJobSpec extends ObjectBehavior
{
    public function let(CountJobQueryInterface $countJobQuery)
    {
        $this->beConstructedWith($countJobQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SearchJob::class);
    }

    public function it_returns_search_job_result(CountJobQueryInterface $countJobQuery)
    {
        $countJobQuery->all()->willReturn(10);
        $expectedSearchJobResult = new SearchJobResult([], 0, 10);
        $this->search()->shouldBeLike($expectedSearchJobResult);
    }
}
