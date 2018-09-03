<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference;

use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference\ChannelIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ChannelReference\NoChannel;
use PhpSpec\ObjectBehavior;

class NoChannelSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NoChannel::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(null);
    }

    function it_tells_if_it_is_equal_to_another_reference()
    {
        $this->equals(NoChannel::create())->shouldReturn(true);
        $this->equals(ChannelIdentifier::fromCode('name_designer_fingerprint'))->shouldReturn(false);
    }
}
