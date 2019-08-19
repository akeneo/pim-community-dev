<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class TextareaPresenterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_change_if_it_has_a_text_key()
    {
        $this->supportsChange('pim_catalog_textarea')->shouldBe(true);
    }

    function it_presents_text_change_using_the_injected_renderer(
        RendererInterface $renderer,
        ValueInterface $value
    ) {
        $value->getData()->willReturn('bar');
        $value->getAttributeCode()->willReturn('color');
        $renderer->renderDiff(['bar'], ['foo'])->willReturn('diff between bar and foo');

        $this->setRenderer($renderer);
        $this->present($value, ['data' => 'foo'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_before_rendering_diff(
        RendererInterface $renderer,
        ValueInterface $value
    ) {
        $value->getData()->willReturn('<p>foo</p> <p>bar</p>');
        $value->getAttributeCode()->willReturn('color');
        $renderer->renderDiff(['<p>foo</p>','<p>bar</p>'],['<p>foo</p>'])->willReturn('diff between bar and foo');

        $this->setRenderer($renderer);
        $this->present($value, ['data' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_without_space_before_rendering_diff(
        RendererInterface $renderer,
        ValueInterface $value
    ) {
        $value->getData()->willReturn('<p>foo</p><p>bar</p>');
        $value->getAttributeCode()->willReturn('color');
        $renderer->renderDiff(['<p>foo</p>','<p>bar</p>'],['<p>foo</p>'])->willReturn('diff between bar and foo');

        $this->setRenderer($renderer);
        $this->present($value, ['data' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }
}
