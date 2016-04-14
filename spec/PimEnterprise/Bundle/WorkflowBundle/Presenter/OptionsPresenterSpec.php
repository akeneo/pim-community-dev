<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class OptionsPresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository)
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
        ProductValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $red,
        AttributeOptionInterface $green,
        AttributeOptionInterface $blue
    ) {
        $repository->findOneByIdentifier('color.red')->willReturn($red);
        $repository->findOneByIdentifier('color.green')->willReturn($green);
        $repository->findOneByIdentifier('color.blue')->willReturn($blue);
        $value->getData()->willReturn([$red, $green]);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
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
