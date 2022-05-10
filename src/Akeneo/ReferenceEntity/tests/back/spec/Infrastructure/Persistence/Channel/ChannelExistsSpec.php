<?php

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Channel;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use PhpSpec\ObjectBehavior;

class ChannelExistsSpec extends ObjectBehavior
{
    public function let(FindChannels $findChannels)
    {
        $channelEcommerce = new Channel('ecommerce', [], LabelCollection::fromArray([]), []);
        $channelPrint = new Channel('print', [], LabelCollection::fromArray([]), []);

        $findChannels->findAll()->willReturn([
            $channelEcommerce,
            $channelPrint,
        ]);

        $this->beConstructedWith($findChannels);
    }

    public function it_tells_if_a_channel_exists()
    {
        $ecommerceIdentifier = ChannelIdentifier::fromCode('ecommerce');
        $this->exists($ecommerceIdentifier)->shouldReturn(true);

        $printIdentifier = ChannelIdentifier::fromCode('print');
        $this->exists($printIdentifier)->shouldReturn(true);

        $unknownIdentifier = ChannelIdentifier::fromCode('unknown');
        $this->exists($unknownIdentifier)->shouldReturn(false);
    }

    public function it_is_case_insensitive()
    {
        $ecommerceIdentifier = ChannelIdentifier::fromCode('eCoMmErCe');
        $this->exists($ecommerceIdentifier)->shouldReturn(true);

        $ecommerceIdentifier = ChannelIdentifier::fromCode('ECOMMERCE');
        $this->exists($ecommerceIdentifier)->shouldReturn(true);
    }
}
