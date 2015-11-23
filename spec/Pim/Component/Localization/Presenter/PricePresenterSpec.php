<?php

namespace spec\Pim\Component\Localization\Presenter;

use PhpSpec\ObjectBehavior;

class PricePresenterSpec extends ObjectBehavior
{
    function it_should_present_english_price()
    {
        $this
            ->present(['data' => 12.34, 'currency' => 'USD'], ['locale' => 'en_US'])
            ->shouldReturn('$12.34');
    }

    function it_should_present_french_price()
    {
        $this
            ->present(['data' => 12.34, 'currency' => 'USD'], ['locale' => 'fr_FR'])
            ->shouldReturn('12,34 $US');
    }

    function it_should_present_english_negative_price()
    {
        $this
            ->present(['data' => -12.34, 'currency' => 'USD'], ['locale' => 'en_US'])
            ->shouldReturn('-$12.34');
    }

    function it_should_present_french_negative_price()
    {
        $this
            ->present(['data' => -12.34, 'currency' => 'USD'], ['locale' => 'fr_FR'])
            ->shouldReturn('-12,34 $US');
    }

    function it_should_present_price_without_option()
    {
        $this
            ->present(['data' => 12.34, 'currency' => 'USD'])
            ->shouldReturn('12.34 $');
    }
}
