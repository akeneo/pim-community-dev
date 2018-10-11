<?php

namespace Specification\Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Factory\ChannelConfigurationFactory;
use Akeneo\Asset\Component\Model\ChannelVariationsConfiguration;
use PhpSpec\ObjectBehavior;

class ChannelConfigurationFactorySpec extends ObjectBehavior
{
    const CONFIG_CLASS = ChannelVariationsConfiguration::class;

    function let()
    {
        $this->beConstructedWith(self::CONFIG_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType(ChannelConfigurationFactory::class);
    }

    function it_creates_a_channel_configuration()
    {
        $this->createChannelConfiguration()->shouldReturnAnInstanceOf(self::CONFIG_CLASS);
    }
}
