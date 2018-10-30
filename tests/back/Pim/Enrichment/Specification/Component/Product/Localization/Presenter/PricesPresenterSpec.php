<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use PhpSpec\ObjectBehavior;

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
            ->present([['amount' => 12.34, 'currency' => 'USD']], ['locale' => 'en_US'])
            ->shouldReturn('$12.34');
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
            ->present([['amount' => 12.34, 'currency' => 'USD']], ['locale' => 'fr_FR'])
            ->shouldReturn('12,34 $US');
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
            ->present([['amount' => -12.34, 'currency' => 'USD']], ['locale' => 'en_US'])
            ->shouldReturn('-$12.34');
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
            ->present(['amount' => -12.34, 'currency' => 'USD'], ['locale' => 'fr_FR'])
            ->shouldReturn('-12,34 $US');
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
            ->present([['amount' => 12.34, 'currency' => 'USD']])
            ->shouldReturn('$12.34');
    }

    function it_returns_all_prices_as_a_string(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(125, 'USD')->willReturn('$125');
        $numberFormatter->formatCurrency(123.5, 'EUR')->willReturn('€123.5');

        $this
            ->present([['amount' => 125, 'currency' => 'USD'], ['amount' => 123.5, 'currency' => 'EUR']])
            ->shouldReturn('$125, €123.5');
    }

    function it_returns_an_empty_string_if_data_is_null(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory
            ->create(['type' => \NumberFormatter::CURRENCY])
            ->willReturn($numberFormatter);
        $numberFormatter->formatCurrency(null, 'USD')->shouldNotBeCalled();

        $this
            ->present([['amount' => null, 'currency' => 'USD']])
            ->shouldReturn('');
    }
}
