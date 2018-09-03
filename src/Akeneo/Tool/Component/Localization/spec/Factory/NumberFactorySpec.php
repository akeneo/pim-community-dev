<?php

namespace spec\Akeneo\Tool\Component\Localization\Factory;

use PhpSpec\ObjectBehavior;

class NumberFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'zz_ZZ' => '#,##0.00-test-¤;(#,##0.00¤)'
        ]);
    }

    function it_creates_a_default_currency_formatter()
    {
        $this
            ->create(['locale' => 'fr_FR', 'type' => \NumberFormatter::CURRENCY])
            ->formatCurrency(12.34, 'EUR')
            ->shouldReturn('12,34 €');
    }

    function it_creates_a_defined_currency_formatter()
    {
        $this
            ->create(['locale' => 'zz_ZZ', 'type' => \NumberFormatter::CURRENCY])
            ->formatCurrency(12.34, 'EUR')
            ->shouldReturn('12.34-test-€');
        $this
            ->create(['locale' => 'zz_ZZ', 'type' => \NumberFormatter::CURRENCY])
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
