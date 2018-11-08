<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Exception\RendererRequiredException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class RendererRegistrySpec extends ObjectBehavior
{
    function it_renders_a_document_with_the_right_renderer(RendererInterface $renderer, ProductInterface $blender)
    {
        $this->addRenderer($renderer);

        $renderer->supports($blender, 'pdf')->willReturn(true);
        $renderer->render($blender, 'pdf', [])->willReturn('I am a nice blender!');

        $this->render($blender, 'pdf', [])->shouldReturn('I am a nice blender!');
    }

    function it_renders_a_document_with_the_renderer_which_has_the_higher_priority(
        RendererInterface $renderer,
        RendererInterface $betterRenderer,
        ProductInterface $blender
    ) {
        $this->addRenderer($betterRenderer);
        $this->addRenderer($renderer);

        $renderer->supports($blender, 'pdf')->willReturn(true);

        $betterRenderer->supports($blender, 'pdf')->willReturn(true);
        $betterRenderer->render($blender, 'pdf', [])->willReturn('I am a nicer blender!');

        $this->render($blender, 'pdf', [])->shouldReturn('I am a nicer blender!');
    }

    function it_renders_a_document_with_the_renderer_which_is_compatible(
        RendererInterface $renderer,
        RendererInterface $betterRenderer,
        ProductInterface $blender
    ) {
        $this->addRenderer($betterRenderer);
        $this->addRenderer($renderer);

        $renderer->supports($blender, 'pdf')->willReturn(true);
        $renderer->render($blender, 'pdf', [])->willReturn('I am a nice blender!');

        $betterRenderer->supports($blender, 'pdf')->willReturn(false);

        $this->render($blender, 'pdf', [])->shouldReturn('I am a nice blender!');
    }

    function it_throws_an_exception_if_there_is_no_renderer_available(
        RendererInterface $renderer,
        RendererInterface $betterRenderer,
        ProductInterface $blender
    ) {
        $this->addRenderer($betterRenderer);
        $this->addRenderer($renderer);

        $renderer->supports($blender, 'pdf')->willReturn(false);
        $betterRenderer->supports($blender, 'pdf')->willReturn(false);

        $this->shouldThrow(RendererRequiredException::class)->during('render', [$blender, 'pdf', []]);
    }
}
