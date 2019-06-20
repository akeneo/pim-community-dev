<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
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

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(true);
    }

    function it_tells_if_it_is_true()
    {
        $this->isTrue()->shouldReturn(true);
    }
}
