<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;

class OptionsPresenterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_translator_aware_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_multiselect()
    {
        $this->supportsChange('pim_catalog_multiselect')->shouldBe(true);
    }

    function it_presents_options_change_using_the_injected_renderer(
        $repository,
        RendererInterface $renderer,
        ValueInterface $value,
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
