<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Value;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use PhpSpec\ObjectBehavior;

class TableSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                ['foo' => 'bar'],
                ['bar' => 'baz'],
            ],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Table::class);
    }

    function it_cannot_be_instantiated_with_an_empty_array()
    {
        $this->beConstructedThrough('fromNormalized', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_non_array_rows()
    {
        $this->beConstructedThrough('fromNormalized', [['123', false]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn(
            [
                ['foo' => 'bar'],
                ['bar' => 'baz'],
            ]
        );
    }
}
