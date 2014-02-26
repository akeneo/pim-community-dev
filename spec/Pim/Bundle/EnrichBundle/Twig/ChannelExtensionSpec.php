<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\EnrichBundle\Provider\ColorsProvider;

class ChannelExtensionSpec extends ObjectBehavior
{
    function let(ChannelManager $manager, ColorsProvider $colorsProvider)
    {
        $this->beConstructedWith($manager, $colorsProvider);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_provides_a_channel_color_twig_function()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions->shouldHaveKey('channel_color');
        $functions->shouldHaveKey('channel_font_color');
        $functions['channel_color']->shouldBeAnInstanceOf('\Twig_Function_Method');
        $functions['channel_font_color']->shouldBeAnInstanceOf('\Twig_Function_Method');
    }

    function its_channelColor_method_returns_the_color_for_the_provided_channel_code($manager, Channel $channel, $colorsProvider)
    {
        $manager->getChannelByCode(Argument::not(null))->willReturn($channel);
        $channel->getColor()->willReturn('blue');
        $colorsProvider->getColorCode('blue')->willReturn('0,31,63,.4');

        $this->channelColor('test')->shouldReturn('0,31,63,.4');
    }

    function its_channelColor_method_returns_an_empty_string_if_code_is_null_or_channel_is_not_found($manager)
    {
        $manager->getChannelByCode(Argument::any())->willReturn(null);

        $this->channelColor('test')->shouldReturn('');
        $this->channelColor(null)->shouldReturn('');
    }

    function its_channelFontColor_method_returns_the_font_color_for_the_provided_channel_code($manager, Channel $channel, $colorsProvider)
    {
        $manager->getChannelByCode(Argument::not(null))->willReturn($channel);

        $channel->getColor()->willReturn('blue');
        $colorsProvider->getColorCode('blue')->willReturn('0,31,63,.4');
        $colorsProvider->getFontColor('blue')->willReturn('#ccc');

        $this->channelFontColor('test')->shouldReturn('#ccc');
    }

    function its_channelFontColor_method_returns_an_empty_string_if_code_is_null_or_channel_is_not_found($manager)
    {
        $manager->getChannelByCode(Argument::any())->willReturn(null);

        $this->channelFontColor('green')->shouldReturn('');
        $this->channelFontColor(null)->shouldReturn('');
    }
}
