<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use PhpSpec\ObjectBehavior;

class AttributeMaxFileSizeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['10.0']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeMaxFileSize::class);
    }

    function it_can_have_no_limit()
    {
        $this->beConstructedThrough('noLimit', []);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('10.0');
        $this::noLimit()->normalize()->shouldReturn(null);
    }

    function it_cannot_be_created_from_an_empty_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['']);
    }

    function it_cannot_be_greater_than_the_limit()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['10000.0']);
    }

    function it_cannot_be_negative()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['-1.02']);
    }
}
