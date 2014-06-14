<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MetricPresenterSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $translator->trans(Argument::type('string'))->will(function($args) {
            return 'trans_'.strtolower($args[0]);
        });
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface');
    }

    function it_supports_change_if_it_has_a_metric_key(
        Model\AbstractProductValue $value
    ) {
        $this->supports($value, ['metric' => 'foo'])->shouldBe(true);
    }

    function it_presents_metric_change_using_the_injected_renderer(
        $translator,
        RendererInterface $renderer,
        Model\AbstractProductValue $value,
        Model\Metric $metric
    ) {
        $value->getData()->willReturn($metric);
        $metric->getData()->willReturn(50);
        $metric->getUnit()->willReturn('KILOGRAM');

        $renderer->renderDiff('50 trans_kilogram', '123 trans_millimeter')->willReturn('diff between two metrics');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present($value, ['metric' => ['unit' => 'MILLIMETER', 'data' => '123']])->shouldReturn('diff between two metrics');
    }

    function it_presents_metric_new_value_even_if_metric_does_not_have_a_value_yet(
        $translator,
        RendererInterface $renderer,
        Model\AbstractProductValue $value
    ) {
        $value->getData()->willReturn(null);

        $renderer->renderDiff('', '123 trans_millimeter')->willReturn('a new metric');

        $this->setRenderer($renderer);
        $this->setTranslator($translator);
        $this->present($value, ['metric' => ['unit' => 'MILLIMETER', 'data' => '123']])->shouldReturn('a new metric');
    }
}
