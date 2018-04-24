<?php

namespace spec\Akeneo\Test\Acceptance\Channel;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Prophecy\Argument;

class InMemoryChannelRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryChannelRepository::class);
    }

    function it_is_a_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_channel()
    {
        $this->save(new Channel())->shouldReturn(null);
    }

    function it_only_saves_channels()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_channel_by_its_identifier()
    {
        $channel = new Channel();
        $channel->setCode('channel');
        $this->save($channel);
        $this->findOneByIdentifier('channel')->shouldReturn($channel);
    }

    function it_returns_null_if_the_channel_does_not_exist()
    {
        $this->findOneByIdentifier('channel')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }
}
