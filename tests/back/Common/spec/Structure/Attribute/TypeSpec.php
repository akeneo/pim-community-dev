<?php

namespace spec\Akeneo\Test\Common\Structure\Attribute;

use Akeneo\Test\Common\Structure\Attribute\Type;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Prophecy\Argument;

class TypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::IDENTIFIER);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Type::class);
    }

    function it_cannot_be_empty()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', ['']);
    }

    function it_is_printable()
    {
        $this->__toString()->shouldReturn(AttributeTypes::IDENTIFIER);
    }
}
