<?php

namespace spec\PimEnterprise\Component\ProductAsset\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;

class VariationFactorySpec extends ObjectBehavior
{
    const VARIATION_CLASS = 'PimEnterprise\Component\ProductAsset\Model\Variation';

    function let()
    {
        $this->beConstructedWith(self::VARIATION_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Factory\VariationFactory');
    }

    function it_creates_a_new_variation_without_channel()
    {
        $this->create()->shouldReturnAnInstanceOf(self::VARIATION_CLASS);
    }

    function it_creates_a_new_variation_with_channel(ChannelInterface $channel)
    {
        $variation = $this->create($channel);
        $variation->shouldBeAnInstanceOf(self::VARIATION_CLASS);
        $variation->getChannel()->shouldReturn($channel);
    }
}
