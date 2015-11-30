<?php

namespace spec\Pim\Component\Localization\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Factory\NumberFactory;

class NumberPresenterSpec extends ObjectBehavior
{
    function let(NumberFactory $numberFactory)
    {
        $this->beConstructedWith($numberFactory, ['pim_catalog_number']);
    }

    function it_supports_numbers()
    {
        $this->supports('pim_catalog_number')->shouldReturn(true);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_presents_english_number(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create([])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12,000.34');
        $this->present(12000.34)->shouldReturn('12,000.34');
    }

    function it_presents_french_number(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create(['locale' => 'fr_FR'])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12 000,34');
        $this->present(12000.34, ['locale' => 'fr_FR'])->shouldReturn('12 000,34');
    }

    function it_disables_grouping_separator(
        $numberFactory,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create(['disable_grouping_separator' => true])->willReturn($numberFormatter);
        $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '')->willReturn(null);
        $numberFormatter->format(12000.34)->willReturn('12000.34');
        $this->present(12000.34, ['disable_grouping_separator' => true])->shouldReturn('12000.34');
    }
}
