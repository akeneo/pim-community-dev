<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use PhpSpec\ObjectBehavior;

class AttributeValuePerChannelSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromBoolean', [true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeValuePerChannel::class);
    }

    function it_tells_if_it_is_yes()
    {
        $this->isYes()->shouldReturn(true);
    }
}
