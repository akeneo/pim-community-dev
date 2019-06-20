<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use PhpSpec\ObjectBehavior;

class AttributeMaxLengthSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromInteger', [300]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeMaxLength::class);
    }

    function it_can_have_no_limit()
    {
        $this->beConstructedThrough('noLimit', []);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(300);
        $this::noLimit()->normalize()->shouldReturn(null);
    }

    function it_returns_an_intValue()
    {
        $this->intValue()->shouldReturn(300);
    }

    function it_cannot_be_greater_than_the_limit()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromInteger', [65536]);
    }

    function it_cannot_be_negative()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromInteger', [-1]);
    }
}
