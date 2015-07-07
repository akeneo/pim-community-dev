<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class OptionsPresenterSpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_multiselect()
    {
        $this->supportsChange('pim_catalog_multiselect')->shouldBe(true);
    }

    function it_presents_options_change_using_the_injected_renderer(
        $repository,
        RendererInterface $renderer,
        Model\ProductValueInterface $value,
        AttributeOptionInterface $red,
        AttributeOptionInterface $green,
        AttributeOptionInterface $blue
    ) {
        $repository->findBy(['code' => ['red', 'green', 'blue']])->willReturn([$red, $green, $blue]);
        $value->getData()->willReturn([$red, $green]);
        $red->__toString()->willReturn('Red');
        $green->__toString()->willReturn('Green');
        $blue->__toString()->willReturn('Blue');

        $renderer
            ->renderDiff(['Red', 'Green'], ['Red', 'Green', 'Blue'])
            ->willReturn('diff between two options collections');

        $this->setRenderer($renderer);
        $this->present($value, ['data' => ['red', 'green', 'blue']])->shouldReturn('diff between two options collections');
    }
}
