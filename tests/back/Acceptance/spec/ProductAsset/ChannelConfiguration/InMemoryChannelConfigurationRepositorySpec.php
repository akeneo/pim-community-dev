<?php

namespace spec\AkeneoEnterprise\Test\Acceptance\ProductAsset\ChannelConfiguration;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use AkeneoEnterprise\Test\Acceptance\ProductAsset\ChannelConfiguration\InMemoryChannelConfigurationRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\Channel;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfiguration;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;
use Prophecy\Argument;

class InMemoryChannelConfigurationRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryChannelConfigurationRepository::class);
    }

    function it_is_an_identifiable_repository()
    {
        $this->shouldBeAnInstanceOf(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldBeAnInstanceOf(SaverInterface::class);
    }

    function it_is_a_rule_definition_repository()
    {
        $this->shouldBeAnInstanceOf(ChannelConfigurationRepositoryInterface::class);
    }

    function it_asserts_the_identifier_property_is_the_code()
    {
        $this->getIdentifierProperties()->shouldReturn(['channel']);
    }

    function it_finds_a_channel_configuration_by_identifier()
    {
        $channel = new Channel();
        $channel->setCode('a-channel');

        $channelConfiguration = new ChannelVariationsConfiguration();
        $channelConfiguration->setChannel($channel);
        $this->beConstructedWith([$channelConfiguration->getChannel()->getCode() => $channelConfiguration]);

        $this->findOneByIdentifier('a-channel')->shouldReturn($channelConfiguration);
    }

    function it_finds_nothing_if_it_does_not_exist()
    {
        $this->findOneByIdentifier('a-non-existing-channel-config')->shouldReturn(null);
    }

    function it_saves_a_channel_configuration()
    {
        $channel = new Channel();
        $channel->setCode('a-channel');

        $channelConfiguration = new ChannelVariationsConfiguration();
        $channelConfiguration->setChannel($channel);

        $this->save($channelConfiguration)->shouldReturn(null);

        $this->findOneByIdentifier('a-channel')->shouldReturn($channelConfiguration);
    }

    function it_saves_only_rule_definitions()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['a_thing']);
    }
}
