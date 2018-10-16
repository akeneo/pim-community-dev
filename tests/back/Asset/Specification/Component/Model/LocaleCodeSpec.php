<?php

namespace Specification\Akeneo\Asset\Component\Model;

use Akeneo\Asset\Component\Model\LocaleCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocaleCodeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('FR_fr');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleCode::class);
    }

    function it_can_constructed_with_an_empty_sting()
    {
        $this->beConstructedWith('');
        $this->hasValidCode()->shouldReturn(true);
    }

    function it_has_a_default_value_because_an_asset_cannot_be_localizable()
    {
        $this->beConstructedWith('no-locale');
        $this->hasValidCode()->shouldReturn(true);
    }

    function it_is_displayable()
    {
        $this->__toString()->shouldReturn('FR_fr');
    }
}
