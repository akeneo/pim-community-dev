<?php

namespace spec\Pim\Bundle\EnrichBundle\Provider;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ColorsProviderSpec extends ObjectBehavior
{
    function let()
    {
        $colors = ['blue' => '0,102,204,.3', 'green' => '153,255,51,.4'];
        $this->beConstructedWith($colors);
    }

    function it_provides_color_choices()
    {
        $this->getColorChoices()->shouldReturn(['blue' => 'color.blue', 'green' => 'color.green']);
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
}
