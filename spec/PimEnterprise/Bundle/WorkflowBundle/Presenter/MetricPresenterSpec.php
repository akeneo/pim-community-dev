<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\Metric;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(
        TranslatorInterface $translator,
        PresenterInterface $metricPresenter,
        LocaleResolver $localeResolver
    ) {
        $translator->trans(Argument::type('string'))->will(function ($args) {
            return 'trans_'.strtolower($args[0]);
        });
        $this->beConstructedWith($metricPresenter, $localeResolver);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface');
    }

    function it_supports_metric()
    {
        $this->supportsChange('pim_catalog_metric')->shouldBe(true);
    }

    function it_presents_metric_change_using_the_injected_renderer(
        $translator,
        $metricPresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value,
        AttributeInterface $attribute,
        Metric $metric
    ) {
        $value->getData()->willReturn($metric);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('size');
        $metric->getData()->willReturn(50.123);
        $metric->getUnit()->willReturn('KILOGRAM');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $metricPresenter
            ->present(['data' => 50.123, 'unit' => 'KILOGRAM'], ['locale' => 'en_US'])
            ->willReturn('50.123 trans_kilogram');
        $metricPresenter
            ->present(['data' => '123.456', 'unit' => 'MILLIMETER'], ['locale' => 'en_US'])
            ->willReturn('123.456 trans_millimeter');

        $renderer->renderDiff('50.123 trans_kilogram', '123.456 trans_millimeter')
            ->willReturn('diff between two metrics');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this
            ->present($value, ['data' => ['unit' => 'MILLIMETER', 'data' => '123.456']])
            ->shouldReturn('diff between two metrics');
    }

    function it_presents_metric_new_value_even_if_metric_does_not_have_a_value_yet(
        $translator,
        $metricPresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getData()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('size');
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $metricPresenter->present(null, ['locale' => 'en_US'])->willReturn(null);
        $metricPresenter
            ->present(['data' => 123.456, 'unit' => 'MILLIMETER'], ['locale' => 'en_US'])
            ->willReturn('123.456 trans_millimeter');

        $renderer->renderDiff('', '123.456 trans_millimeter')->willReturn('a new metric');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present($value, ['data' => ['unit' => 'MILLIMETER', 'data' => '123.456']])
            ->shouldReturn('a new metric');
    }

    function it_presents_french_format_metrics(
        $translator,
        $metricPresenter,
        $localeResolver,
        RendererInterface $renderer,
        ProductValueInterface $value
    ) {
        $localeResolver->getCurrentLocale()->willReturn('fr_FR');
        $renderer->renderDiff('', '150,123456 trans_kilogram')->willReturn("150,123456 trans_kilogram");
        $metricPresenter
            ->present(['data' => 150.123456, 'unit' => 'KILOGRAM'], ['locale' => 'fr_FR'])
            ->willReturn("150,123456 trans_kilogram");

        $this->setRenderer($renderer);
        $this->setTranslator($translator);

        $this->present($value, ['data' => ['data' => 150.123456, 'unit' => 'KILOGRAM']])
            ->shouldReturn("150,123456 trans_kilogram");
    }
}
