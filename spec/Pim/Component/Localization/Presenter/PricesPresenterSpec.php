<?php

namespace spec\Pim\Component\Localization\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Factory\NumberFactory;

class PricesPresenterSpec extends ObjectBehavior
{
    function let(NumberFactory $numberFactory)
    {
        $this->beConstructedWith($numberFactory, ['pim_catalog_price_collection']);
    }

    function it_should_present_english_price(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['locale' => 'en_US', 'type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(12.34, 'USD')->willReturn('$12.34');
        $this
            ->present([['data' => 12.34, 'currency' => 'USD']], ['locale' => 'en_US'])
            ->shouldReturn(['$12.34']);
    }

    function it_should_present_french_price(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['locale' => 'fr_FR', 'type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(12.34, 'USD')->willReturn('12,34 $US');
        $this
            ->present([['data' => 12.34, 'currency' => 'USD']], ['locale' => 'fr_FR'])
            ->shouldReturn(['12,34 $US']);
    }

    function it_should_present_english_negative_price(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['locale' => 'en_US', 'type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(-12.34, 'USD')->willReturn('-$12.34');
        $this
            ->present([['data' => -12.34, 'currency' => 'USD']], ['locale' => 'en_US'])
            ->shouldReturn(['-$12.34']);
    }

    function it_should_present_french_negative_price(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['locale' => 'fr_FR', 'type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(-12.34, 'USD')->willReturn('-12,34 $US');
        $this
            ->present([['data' => -12.34, 'currency' => 'USD']], ['locale' => 'fr_FR'])
            ->shouldReturn(['-12,34 $US']);
    }

    function it_should_present_price_without_option(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(12.34, 'USD')->willReturn('$12.34');
        $this
            ->present([['data' => 12.34, 'currency' => 'USD']])
            ->shouldReturn(['$12.34']);
    }
}
