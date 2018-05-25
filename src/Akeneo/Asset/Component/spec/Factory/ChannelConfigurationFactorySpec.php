<?php

namespace spec\Akeneo\Asset\Component\Factory;

use PhpSpec\ObjectBehavior;

class ChannelConfigurationFactorySpec extends ObjectBehavior
{
    const CONFIG_CLASS = 'Akeneo\Asset\Component\Model\ChannelVariationsConfiguration';

    function let()
    {
        $this->beConstructedWith(self::CONFIG_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Asset\Component\Factory\ChannelConfigurationFactory');
    }

    function it_creates_a_channel_configuration()
    {
        $this->createChannelConfiguration()->shouldReturnAnInstanceOf(self::CONFIG_CLASS);
    }
}
