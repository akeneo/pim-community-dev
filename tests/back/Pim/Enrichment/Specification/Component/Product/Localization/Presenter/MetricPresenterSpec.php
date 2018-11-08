<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\TranslatorProxy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(NumberFactory $numberFactory, TranslatorProxy $translatorProxy)
    {
        $this->beConstructedWith($numberFactory, ['pim_catalog_metric'], $translatorProxy);
    }

    function it_supports_metric()
    {
        $this->supports('pim_catalog_metric')->shouldReturn(true);
        $this->supports('foobar')->shouldReturn(false);
    }

    function it_presents_english_metric(
        $numberFactory,
        $translatorProxy,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create([])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12,000.34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(null);
        $translatorProxy->trans('pim_measure.units.KILOGRAM')->willReturn('Kilogram');
        $this
            ->present(['amount' => 12000.34, 'unit' => 'KILOGRAM'])
            ->shouldReturn('12,000.34 Kilogram');
    }

    function it_presents_french_metric(
        $numberFactory,
        $translatorProxy,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create(['locale' => 'fr_FR'])->willReturn($numberFormatter);
        $numberFormatter->format(12000.34)->willReturn('12 000,34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(null);
        $translatorProxy->trans('pim_measure.units.KILOGRAM')->willReturn('Kilogram');
        $this
            ->present(['amount' => 12000.34, 'unit' => 'KILOGRAM'], ['locale' => 'fr_FR'])
            ->shouldReturn('12 000,34 Kilogram');
    }

    function it_disables_grouping_separator(
        $numberFactory,
        $translatorProxy,
        \NumberFormatter $numberFormatter
    ) {
        $numberFactory->create(['disable_grouping_separator' => true])->willReturn($numberFormatter);
        $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '')->willReturn(null);
        $numberFormatter->format(12000.34)->willReturn('12000.34');
        $numberFormatter->setAttribute(Argument::any(), Argument::any())->willReturn(null);
        $translatorProxy->trans('pim_measure.units.KILOGRAM')->willReturn('Kilogram');
        $this
            ->present(['amount' => 12000.34, 'unit' => 'KILOGRAM'], ['disable_grouping_separator' => true])
            ->shouldReturn('12000.34 Kilogram');
    }
}
