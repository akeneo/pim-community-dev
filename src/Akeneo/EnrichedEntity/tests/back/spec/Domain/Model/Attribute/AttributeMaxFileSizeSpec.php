<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
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

    function it_cannot_be_greater_than_the_limit()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['10000.0']);
    }

    function it_cannot_be_negative()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', ['-1.02']);
    }

    function it_normalize_itself()
    {
        $this->normalize()->shouldReturn('10.0');
    }
}
