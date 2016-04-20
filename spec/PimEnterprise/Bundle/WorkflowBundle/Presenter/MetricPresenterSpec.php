<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $translator->trans(Argument::type('string'))->will(function ($args) {
            return 'trans_'.strtolower($args[0]);
        });
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
        RendererInterface $renderer,
        ProductValueInterface $value,
        AttributeInterface $attribute,
        Metric $metric
    ) {
        $value->getData()->willReturn($metric);
        $metric->getData()->willReturn(50);
        $metric->getUnit()->willReturn('KILOGRAM');
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('size');

        $renderer->renderDiff('50 trans_kilogram', '123 trans_millimeter')->willReturn('diff between two metrics');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this
            ->present($value, ['data' => ['unit' => 'MILLIMETER', 'data' => '123']])
            ->shouldReturn('diff between two metrics');
    }

    function it_presents_metric_new_value_even_if_metric_does_not_have_a_value_yet(
        $translator,
        RendererInterface $renderer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $value->getData()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('size');

        $renderer->renderDiff('', '123 trans_millimeter')->willReturn('a new metric');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present($value, ['data' => ['unit' => 'MILLIMETER', 'data' => '123']])->shouldReturn('a new metric');
    }
}
