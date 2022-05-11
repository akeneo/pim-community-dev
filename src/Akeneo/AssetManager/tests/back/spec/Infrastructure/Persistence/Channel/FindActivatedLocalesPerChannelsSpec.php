<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Channel;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use PhpSpec\ObjectBehavior;

class FindActivatedLocalesPerChannelsSpec extends ObjectBehavior
{
    public function let(FindChannels $findChannels)
    {
        $channelEcommerce = new Channel('ecommerce', ['fr_FR', 'en_US'], LabelCollection::fromArray([]), []);
        $channelSocial = new Channel('social', ['jp_JP'], LabelCollection::fromArray([]), []);
        $channelPrint = new Channel('print', [], LabelCollection::fromArray([]), []);

        $findChannels->findAll()->willReturn([
            $channelEcommerce,
            $channelSocial,
            $channelPrint,
        ]);

        $this->beConstructedWith($findChannels);
    }

    public function it_returns_activated_locales_per_channels()
    {
        $this->findAll()->shouldReturn([
            'ecommerce' => ['fr_FR', 'en_US'],
            'social' => ['jp_JP'],
            'print' => [],
        ]);
    }
}
