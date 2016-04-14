<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class OptionPresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository)
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
        ProductValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $blue,
        AttributeOptionInterface $red
    ) {
        $repository->findOneByIdentifier('color.blue')->willReturn($blue);
        $value->getData()->willReturn($red);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $red->__toString()->willReturn('Red');
        $blue->__toString()->willReturn('Blue');

        $renderer->renderDiff('Red', 'Blue')->willReturn('diff between two options');

        $this->setRenderer($renderer);
        $this->present($value, ['data' => 'blue'])->shouldReturn('diff between two options');
    }
}
