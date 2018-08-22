<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
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

    function it_can_be_infinite()
    {
        $this->beConstructedThrough('infinite', []);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(300);
        $this::infinite()->normalize()->shouldReturn(null);
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
