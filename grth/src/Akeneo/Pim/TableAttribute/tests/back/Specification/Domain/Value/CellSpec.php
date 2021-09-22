<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Value;

use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use PhpSpec\ObjectBehavior;

class CellSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', ['foo bar']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Cell::class);
    }

    function it_cannot_be_instantiated_with_an_empty_string()
    {
        $this->beConstructedThrough('fromNormalized', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn('foo bar');
    }
}
