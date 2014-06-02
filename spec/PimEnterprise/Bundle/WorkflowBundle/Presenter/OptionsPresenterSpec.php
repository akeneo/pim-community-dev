<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class OptionsPresenterSpec extends ObjectBehavior
{
    function let(AttributeOptionRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_an_options_key(
        Model\AbstractProductValue $value
    ) {
        $this->supports($value, ['options' => '1,2,3'])->shouldBe(true);
    }

    function it_presents_options_change_using_the_injected_renderer(
        $repository,
        RendererInterface $renderer,
        Model\AbstractProductValue $value,
        AttributeOption $red,
        AttributeOption $green,
        AttributeOption $blue
    ) {
        $repository->findBy(['id' => ['1', '2', '3']])->willReturn([$red, $green, $blue]);
        $value->getData()->willReturn([$red, $green]);
        $red->__toString()->willReturn('Red');
        $green->__toString()->willReturn('Green');
        $blue->__toString()->willReturn('Blue');

        $renderer->renderDiff(['Red', 'Green'], ['Red', 'Green', 'Blue'])->willReturn('diff between two options collections');

        $this->setRenderer($renderer);
        $this->present($value, ['options' => '1,2,3'])->shouldReturn('diff between two options collections');
    }
}
