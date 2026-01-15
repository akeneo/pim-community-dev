<?php

namespace spec\Akeneo\Tool\Component\Localization\Factory;

use PhpSpec\ObjectBehavior;

class NumberFactorySpec extends ObjectBehavior
{
    function let()
    {
        // Use a valid locale (en_US) with a custom format to test format overrides
        // PHP 8.4+ throws ValueError for invalid locales like 'zz_ZZ'
        $this->beConstructedWith([
            'en_US' => '#,##0.00-test-¤;(#,##0.00¤)'
        ]);
    }

    function it_creates_a_default_currency_formatter()
    {
        $formatter = $this->create(['locale' => 'fr_FR', 'type' => \NumberFormatter::CURRENCY]);
        $result = $formatter->formatCurrency(12.34, 'EUR');
        // The result should contain 12,34 and € but spacing may vary by ICU version
        $result->shouldMatch('/12,34.*€/');
    }

    function it_creates_a_defined_currency_formatter()
    {
        // Using en_US with custom format override instead of invalid zz_ZZ locale
        $this
            ->create(['locale' => 'en_US', 'type' => \NumberFormatter::CURRENCY])
            ->formatCurrency(12.34, 'EUR')
            ->shouldReturn('12.34-test-€');
        $this
            ->create(['locale' => 'en_US', 'type' => \NumberFormatter::CURRENCY])
            ->formatCurrency(-12.34, 'EUR')
            ->shouldReturn('(12.34€)');
    }

    function it_creates_without_locale()
    {
        $this
            ->create(['type' => \NumberFormatter::CURRENCY])
            ->formatCurrency(12.34, 'EUR')
            ->shouldReturn('€12.34');
    }
}
