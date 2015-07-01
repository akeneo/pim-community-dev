<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class OptionPresenterSpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_simpleselect()
    {
        $this->supportsChange('pim_catalog_simpleselect')->shouldBe(true);
    }

    function it_presents_option_change_using_the_injected_renderer(
        $repository,
        RendererInterface $renderer,
        Model\ProductValueInterface $value,
        AttributeOptionInterface $blue,
        AttributeOptionInterface $red
    ) {
        $repository->findOneBy(['code' => 'blue'])->willReturn($blue);
        $value->getData()->willReturn($red);
        $red->__toString()->willReturn('Red');
        $blue->__toString()->willReturn('Blue');

        $renderer->renderDiff('Red', 'Blue')->willReturn('diff between two options');

        $this->setRenderer($renderer);
        $this->present($value, ['value' => 'blue'])->shouldReturn('diff between two options');
    }
}
