<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as LocalizationPresenter;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(
        TranslatorInterface $translator,
        LocalizationPresenter $metricPresenter,
        LocaleResolver $localeResolver
    ) {
        $translator->trans(Argument::type('string'))->will(function ($args) {
            return 'trans_'.strtolower($args[0]);
        });
        $this->beConstructedWith($metricPresenter, $localeResolver);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
        $this->shouldBeAnInstanceOf(TranslatorAwareInterface::class);
    }

    function it_supports_metric()
    {
        $this->supports('pim_catalog_metric')->shouldBe(true);
    }

    function it_presents_metric_change_using_the_injected_renderer(
        $translator,
        $metricPresenter,
        $localeResolver,
        Metric $metric
    ) {
        $metric->getData()->willReturn(50.123);
        $metric->getUnit()->willReturn('KILOGRAM');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $metricPresenter
            ->present(['amount' => 50.123, 'unit' => 'KILOGRAM'], ['locale' => 'en_US', 'attribute' => 'attributeCode'])
            ->willReturn('50.123 trans_kilogram');
        $metricPresenter
            ->present(['amount' => '123.456', 'unit' => 'MILLIMETER'], ['locale' => 'en_US', 'attribute' => 'attributeCode'])
            ->willReturn('123.456 trans_millimeter');

        $this->setTranslator($translator);
        $this
            ->present($metric, ['data' => ['unit' => 'MILLIMETER', 'amount' => '123.456'], 'attribute' => 'attributeCode'])
            ->shouldReturn([
                'before' => '50.123 trans_kilogram',
                'after' => '123.456 trans_millimeter'
            ]);
    }

    function it_presents_metric_new_value_even_if_metric_does_not_have_a_value_yet(
        $translator,
        $metricPresenter,
        $localeResolver
    ) {
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $metricPresenter->present(null, ['locale' => 'en_US'])->willReturn(null);
        $metricPresenter
            ->present(['amount' => 123.456, 'unit' => 'MILLIMETER'], ['locale' => 'en_US', 'attribute' => 'attributeCode'])
            ->willReturn('123.456 trans_millimeter');

        $this->setTranslator($translator);
        $this->present(null, ['data' => ['unit' => 'MILLIMETER', 'amount' => '123.456'], 'attribute' => 'attributeCode'])
            ->shouldReturn([
                'before' => '',
                'after' => '123.456 trans_millimeter',
            ]);
    }

    function it_presents_french_format_metrics(
        $translator,
        $metricPresenter,
        $localeResolver
    ) {
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
        $metricPresenter
            ->present(['amount' => 150.123456, 'unit' => 'KILOGRAM'], ['locale' => 'fr_FR', 'attribute' => 'attributeCode'])
            ->willReturn("150,123456 trans_kilogram");

        $this->setTranslator($translator);

        $this->present(null, ['data' => ['amount' => 150.123456, 'unit' => 'KILOGRAM'], 'attribute' => 'attributeCode'])
            ->shouldReturn([
                'before' => '',
                'after' => "150,123456 trans_kilogram",
            ]);
    }
}
