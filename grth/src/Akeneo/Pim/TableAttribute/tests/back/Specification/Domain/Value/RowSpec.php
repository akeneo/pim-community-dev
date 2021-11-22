<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Value;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'foo_64fdf664-ad55-4cc7-bbf7-466efb950173' => 'bar',
            'empty_64fdf664-ad55-4cc7-bbf7-466efb950173' => '',
            '0_64fdf664-ad55-4cc7-bbf7-466efb950173' => 'baz',
            'null_64fdf664-ad55-4cc7-bbf7-466efb950173' => null,
        ]]);
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
            'foo_64fdf664-ad55-4cc7-bbf7-466efb950173' => 'bar',
            '0_64fdf664-ad55-4cc7-bbf7-466efb950173' => 'baz',
        ]);
    }

    function it_exposes_its_column_ids()
    {
        $this->columnIds()->shouldReturn(['foo_64fdf664-ad55-4cc7-bbf7-466efb950173', '0_64fdf664-ad55-4cc7-bbf7-466efb950173']);
    }

    function it_returns_the_cell_given_a_column_id()
    {
        $this->cell(ColumnId::fromString('0_64fdf664-ad55-4cc7-bbf7-466efb950173'))
            ->shouldBeLike(Cell::fromNormalized('baz'));
        $this->cell(ColumnId::fromString('foo_64fdf664-ad55-4cc7-bbf7-466efb950173'))
            ->shouldBeLike(Cell::fromNormalized('bar'));
        $this->cell(ColumnId::fromString('FOo_64fdf664-ad55-4cc7-bbf7-466efb950173'))
            ->shouldBeLike(Cell::fromNormalized('bar'));
        $this->cell(ColumnId::fromString('unknown_64fdf664-ad55-4cc7-bbf7-466efb950173'))
            ->shouldReturn(null);
    }

    function it_is_countable()
    {
        $this->count()->shouldReturn(2);
    }
}
