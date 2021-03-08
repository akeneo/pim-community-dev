<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\IdentifiersForQueryResult;
use PhpSpec\ObjectBehavior;

class IdentifiersForQueryResultSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], 5, ['value1']);
        $this->shouldHaveType(IdentifiersForQueryResult::class);
    }

    function it_normalizes_itself()
    {
        $this->beConstructedWith(['starck'], 1, ['value1']);
        $this->normalize()->shouldReturn(['identifiers' => ['starck'], 'matches_count' => 1]);
    }

    function it_can_be_constructed_only_with_a_list_identifiers()
    {
        $this->beConstructedWith([1, 2], 5);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
