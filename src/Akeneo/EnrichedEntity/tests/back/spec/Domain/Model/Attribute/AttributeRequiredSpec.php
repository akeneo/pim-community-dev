<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use PhpSpec\ObjectBehavior;

class AttributeRequiredSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRequired::class);
    }

    function it_tells_if_it_is_yes()
    {
        $this->normalize()->shouldReturn(true);
    }
}
