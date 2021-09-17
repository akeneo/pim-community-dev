<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use PhpSpec\ObjectBehavior;

class IdentifiersForQueryResultSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], 5, null);
        $this->shouldHaveType(IdentifiersForQueryResult::class);
    }

    function it_normalizes_itself()
    {
        $this->beConstructedWith(['starck'], 1, null);
        $this->normalize()->shouldReturn(['identifiers' => ['starck'], 'matches_count' => 1]);
    }

    function it_can_be_constructed_only_with_a_list_identifiers()
    {
        $this->beConstructedWith([1, 2], 5, null);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_hold_the_last_sort_value()
    {
        $this->beConstructedWith([], 0, 'last_sort_value');
        $this->lastSortValue->shouldBe('last_sort_value');
    }
}
