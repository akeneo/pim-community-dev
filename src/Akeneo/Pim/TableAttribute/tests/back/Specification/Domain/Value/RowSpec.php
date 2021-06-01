<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Value;

use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromNormalized', [['foo' => 'bar', 'bar' => 'baz']]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Row::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_array()
    {
        $this->beConstructedThrough('fromNormalized', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
