<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionTableResult;
use PhpSpec\ObjectBehavior;

class SearchJobExecutionTableResultSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith([], 5, 10);
        $this->shouldBeAnInstanceOf(SearchJobExecutionTableResult::class);
    }

    public function it_normalizes_itself()
    {
        $jobExecutionRow = new JobExecutionRow();
        $this->beConstructedWith([$jobExecutionRow], 1, 2);
        $this->normalize()->shouldReturn([
            'items'         => [
                [],
            ],
            'matches_count' => 1,
            'total_count'   => 2,
        ]);
    }

    public function it_can_be_constructed_only_with_a_list_of_asset_items()
    {
        $this->beConstructedWith([1], 5, 10);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
