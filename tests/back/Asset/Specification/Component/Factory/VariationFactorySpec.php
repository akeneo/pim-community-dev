<?php

namespace Specification\Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Factory\VariationFactory;
use Akeneo\Asset\Component\Model\Variation;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;

class VariationFactorySpec extends ObjectBehavior
{
    const VARIATION_CLASS = Variation::class;

    function let()
    {
        $this->beConstructedWith(self::VARIATION_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType(VariationFactory::class);
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
