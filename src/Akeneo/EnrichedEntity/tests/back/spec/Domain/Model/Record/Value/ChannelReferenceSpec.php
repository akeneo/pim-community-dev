<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use PhpSpec\ObjectBehavior;

class ChannelReferenceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromChannelIdentifier', [ChannelIdentifier::fromCode('mobile')]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelReference::class);
    }

    function it_can_be_constructed_with_no_channel()
    {
        $this->beConstructedThrough('noReference', []);
    }

    function it_can_be_constructed_with_a_channel_code()
    {
        $this->beConstructedThrough('createFromNormalized', ['mobile']);
        $this->normalize()->shouldReturn('mobile');
    }

    function it_can_be_constructed_with_no_channel_code()
    {
        $this->beConstructedThrough('createFromNormalized', [null]);
        $this->normalize()->shouldReturn(null);
    }

    function it_normalizes_itself_when_instanciated_with_a_channel_identifier()
    {
        $this->normalize()->shouldReturn('mobile');
    }

    function it_normalizes_itself_when_instanciated_with_no_channel()
    {
        $this->beConstructedThrough('noReference', []);
        $this->normalize()->shouldReturn(null);
    }

    function it_tells_if_is_equal_to_another_reference()
    {
        $this->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')))->shouldReturn(true);
        $this->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')))->shouldReturn(false);
        $this->equals(ChannelReference::noReference())->shouldReturn(false);
    }

    function it_tells_if_is_equal_to_empty_reference()
    {
        $this->beConstructedThrough('noReference', []);
        $this->equals(ChannelReference::noReference())->shouldReturn(true);
        $this->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')))->shouldReturn(false);
    }
}
