<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

class ChannelExtensionSpec extends ObjectBehavior
{
    function let(ChannelManager $manager)
    {
        $this->beConstructedWith($manager);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_provides_a_channel_color_twig_function()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(1);
        $functions->shouldHaveKey('channel_color');
        $functions['channel_color']->shouldBeAnInstanceOf('\Twig_Function_Method');
    }

    function its_channelColor_method_returns_the_color_for_the_provided_channel_code($manager, Channel $channel)
    {
        $manager->getChannelByCode(Argument::not(null))->willReturn($channel);
        $channel->getColor()->willReturn('0,31,63,.4');

        $this->channelColor('test')->shouldReturn('0,31,63,.4');
    }

    function its_channelColor_method_returns_an_empty_string_if_code_is_null_or_channel_is_not_found($manager)
    {
        $manager->getChannelByCode(Argument::any())->willReturn(null);

        $this->channelColor('test')->shouldReturn('');
        $this->channelColor(null)->shouldReturn('');
    }
}
