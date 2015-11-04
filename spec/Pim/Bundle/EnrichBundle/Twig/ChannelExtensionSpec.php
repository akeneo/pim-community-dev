<?php

namespace spec\Pim\Bundle\EnrichBundle\Twig;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Bundle\EnrichBundle\Provider\ColorsProvider;
use Prophecy\Argument;

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

    function it_provides_the_color_and_font_color_for_the_given_channel($manager, ChannelInterface $channel, $colorsProvider)
    {
        $manager->getChannelByCode(Argument::not(null))->willReturn($channel);
        $channel->getColor()->willReturn('blue');
        $colorsProvider->getColorCode('blue')->willReturn('0,31,63,.4');
        $colorsProvider->getFontColor('blue')->willReturn('#ccc');

        $this->channelColor('test')->shouldReturn('0,31,63,.4');
        $this->channelFontColor('test')->shouldReturn('#ccc');
    }

    function it_returns_an_empty_string_if_code_is_null_or_channel_is_not_found($manager)
    {
        $manager->getChannelByCode(Argument::any())->willReturn(null);

        $this->channelColor('test')->shouldReturn('');
        $this->channelColor(null)->shouldReturn('');

        $this->channelFontColor('green')->shouldReturn('');
        $this->channelFontColor(null)->shouldReturn('');
    }
}
