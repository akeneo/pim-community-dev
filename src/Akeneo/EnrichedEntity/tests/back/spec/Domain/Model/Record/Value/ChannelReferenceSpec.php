<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\Domain\Model\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;
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
        $this->beConstructedThrough('noChannel', []);
    }

    function it_normalizes_itself_when_instanciated_with_a_channel_identifier()
    {
        $this->normalize()->shouldReturn('mobile');
    }

    function it_normalizes_itself_when_instanciated_with_no_channel()
    {
        $this->beConstructedThrough('noChannel', []);
        $this->normalize()->shouldReturn(null);
    }

    function it_tells_if_is_equal_to_another_reference()
    {
        $this->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')))->shouldReturn(true);
        $this->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')))->shouldReturn(false);
        $this->equals(ChannelReference::noChannel())->shouldReturn(false);
    }

    function it_tells_if_is_equal_to_empty_reference()
    {
        $this->beConstructedThrough('noChannel', []);
        $this->equals(ChannelReference::noChannel())->shouldReturn(true);
        $this->equals(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')))->shouldReturn(false);
    }
}
