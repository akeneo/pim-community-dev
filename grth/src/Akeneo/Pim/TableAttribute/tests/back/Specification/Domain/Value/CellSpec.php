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

    function it_can_be_instantiated_with_an_array()
    {
        $this->beConstructedThrough('fromNormalized', [['unit' => 'second', 'amount' => '10']]);
        $this->shouldHaveType(Cell::class);
    }

    function it_cannot_be_instantiated_with_an_empty_string()
    {
        $this->beConstructedThrough('fromNormalized', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_an_empty_array()
    {
        $this->beConstructedThrough('fromNormalized', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized_as_string()
    {
        $this->normalize()->shouldReturn('foo bar');
    }

    function it_can_be_normalized_as_an_array()
    {
        $this->beConstructedThrough('fromNormalized', [['unit' => 'second', 'amount' => '10']]);
        $this->normalize()->shouldReturn(['unit' => 'second', 'amount' => '10']);
    }
}
