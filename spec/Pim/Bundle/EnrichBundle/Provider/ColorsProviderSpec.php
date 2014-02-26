<?php

namespace spec\Pim\Bundle\EnrichBundle\Provider;

use PhpSpec\ObjectBehavior;

class ColorsProviderSpec extends ObjectBehavior
{
    function let()
    {
        $colors = ['blue' => '0,102,204,.3', 'green' => '153,255,51,.4', 'white' => '253,246,227,1'];
        $this->beConstructedWith($colors);
    }

    function it_provides_color_choices()
    {
        $this->getColorChoices()->shouldReturn(
            [
                'blue'  => 'color.blue',
                'green' => 'color.green',
                'white' => 'color.white'
            ]
        );
    }

    function it_provides_color_code_by_name()
    {
        $this->getColorCode('green')->shouldReturn('153,255,51,.4');
    }

    function its_getColorCode_method_returns_an_empty_string_if_the_color_is_not_found()
    {
        $this->getColorCode('red')->shouldReturn('');
        $this->getColorCode(null)->shouldReturn('');
    }

    function its_getFontColor_method_returns_a_dark_font_color_if_light_color_is_passed()
    {
        $this->getFontColor('white')->shouldReturn('#111');
    }

    function its_getFontColor_method_returns_a_light_font_color_if_dark_color_is_passed()
    {
        $this->getFontColor('blue')->shouldReturn('#fff');
    }

    function its_getFontColor_method_returns_an_empty_string_if_the_color_is_not_found()
    {
        $this->getFontColor('red')->shouldReturn('');
        $this->getFontColor(null)->shouldReturn('');
    }
}
