<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Domain\Model;

use Akeneo\Platform\Job\Domain\Model\JobItem;
use Akeneo\Platform\Job\Domain\Model\SearchJobResult;
use PhpSpec\ObjectBehavior;

class SearchJobResultSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith([], 5, 10);
        $this->shouldBeAnInstanceOf(SearchJobResult::class);
    }

    function it_normalizes_itself()
    {
        $jobItem = new JobItem();
        $this->beConstructedWith([$jobItem], 1, 2);
        $this->normalize()->shouldReturn([
            'items'         => [
                [],
            ],
            'matches_count' => 1,
            'total_count'   => 2,
        ]);
    }

    function it_can_be_constructed_only_with_a_list_of_asset_items()
    {
        $this->beConstructedWith([1], 5, 10);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
