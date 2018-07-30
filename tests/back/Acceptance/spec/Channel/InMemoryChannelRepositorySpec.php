<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;

class InMemoryChannelRepositorySpec extends ObjectBehavior
{
    function it_is_a_channel_repository()
    {
        $this->shouldImplement(ChannelRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_returns_an_identifier_property()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_a_channel_by_identifier()
    {
        $channel = $this->createChannel('ecommerce');
        $this->beConstructedWith([$channel->getCode() => $channel]);

        $this->findOneByIdentifier('ecommerce')->shouldReturn($channel);
    }

    function it_does_not_find_a_channel_by_identifier()
    {
        $channel = $this->createChannel('ecommerce');
        $this->beConstructedWith([$channel->getCode() => $channel]);

        $this->findOneByIdentifier('mobile')->shouldReturn(null);
    }

    function it_saves_a_channel()
    {
        $channel = $this->createChannel('ecommerce');
        $this->save($channel);

        $this->findOneByIdentifier('ecommerce')->shouldReturn($channel);
    }

    function it_finds_all_the_channel()
    {
        $channel1 = $this->createChannel('ecommerce');
        $this->save($channel1);

        $channel2 = $this->createChannel('mobile');
        $this->save($channel2);

        $this->findAll()->shouldReturn([$channel1, $channel2]);
    }

    private function createChannel(string $code): ChannelInterface
    {
        $channel = new Channel();
        $channel->setCode($code);

        return $channel;
    }
}
