<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Value;

use Akeneo\Pim\TableAttribute\Domain\Value\CellInterface;
use Akeneo\Pim\TableAttribute\Domain\Value\StringCell;
use PhpSpec\ObjectBehavior;

class StringCellSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromNormalized', ['foo bar']);
    }

    public function it_is_initializable()
    {
        $this->shouldImplement(CellInterface::class);
        $this->shouldHaveType(StringCell::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_string()
    {
        $this->beConstructedThrough('fromNormalized', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
