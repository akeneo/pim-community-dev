<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\Application\SearchJobResult;
use Akeneo\Platform\Job\Domain\Model\JobItem;
use PhpSpec\ObjectBehavior;

class SearchJobResultSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith([], 5, 10);
        $this->shouldBeAnInstanceOf(SearchJobResult::class);
    }

    public function it_normalizes_itself()
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

    public function it_can_be_constructed_only_with_a_list_of_asset_items()
    {
        $this->beConstructedWith([1], 5, 10);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
