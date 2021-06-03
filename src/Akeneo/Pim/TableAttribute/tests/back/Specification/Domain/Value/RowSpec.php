<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Value;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [['foo' => 'bar', '0' => 'baz']]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Row::class);
    }

    function it_cannot_be_instantiated_with_an_empty_array()
    {
        $this->beConstructedThrough('fromNormalized', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
            'foo' => 'bar',
            '0' => 'baz',
        ]);
    }

    function it_exposes_its_column_codes()
    {
        $this->columnCodes()->shouldReturn(['foo', '0']);
    }

    function it_returns_the_cell_given_a_column_code()
    {
        $this->cell(ColumnCode::fromString('0'))->shouldBeLike(Cell::fromNormalized('baz'));
        $this->cell(ColumnCode::fromString('unknown'))->shouldReturn(null);
    }
}
