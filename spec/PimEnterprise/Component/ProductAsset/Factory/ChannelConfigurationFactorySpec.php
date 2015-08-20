<?php

namespace spec\PimEnterprise\Component\ProductAsset\Factory;

use PhpSpec\ObjectBehavior;

class ChannelConfigurationFactorySpec extends ObjectBehavior
{
    const CONFIG_CLASS = 'PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfiguration';

    function let()
    {
        $this->beConstructedWith(self::CONFIG_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Factory\ChannelConfigurationFactory');
    }

    function it_creates_a_channel_configuration()
    {
        $this->createChannelConfiguration()->shouldReturnAnInstanceOf(self::CONFIG_CLASS);
    }
}
