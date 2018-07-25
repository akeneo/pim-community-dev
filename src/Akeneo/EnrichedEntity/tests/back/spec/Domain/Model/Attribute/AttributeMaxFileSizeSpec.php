<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use PhpSpec\ObjectBehavior;

class AttributeMaxFileSizeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromFloat', [10.2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeMaxFileSize::class);
    }

    function it_cannot_be_greater_than_the_limit()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromFloat', [10000.0]);
    }

    function it_cannot_be_negative()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromFloat', [-1.02]);
    }
}
