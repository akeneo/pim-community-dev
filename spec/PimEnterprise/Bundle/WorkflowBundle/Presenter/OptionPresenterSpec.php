<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class OptionPresenterSpec extends ObjectBehavior
{
    function let(AttributeOptionRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_an_option_key(
        Model\AbstractProductValue $value
    ) {
        $this->supports($value, ['option' => '1'])->shouldBe(true);
    }

    function it_presents_option_change_using_the_injected_renderer(
        $repository,
        RendererInterface $renderer,
        Model\AbstractProductValue $value,
        AttributeOption $blue,
        AttributeOption $red
    ) {
        $repository->find('1')->willReturn($blue);
        $value->getData()->willReturn($red);
        $red->__toString()->willReturn('Red');
        $blue->__toString()->willReturn('Blue');

        $renderer->renderDiff('Red', 'Blue')->willReturn('diff between two options');

        $this->setRenderer($renderer);
        $this->present($value, ['option' => '1'])->shouldReturn('diff between two options');
    }
}
