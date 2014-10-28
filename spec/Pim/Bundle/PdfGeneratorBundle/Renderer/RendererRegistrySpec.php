<?php

namespace spec\Pim\Bundle\PdfGeneratorBundle\Renderer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererInterface;

class RendererRegistrySpec extends ObjectBehavior
{
    function it_renders_a_document_with_the_right_renderer(RendererInterface $renderer, AbstractProduct $blender)
    {
        $this->addRenderer($renderer);

        $renderer->supports($blender, 'pdf')->willReturn(true);
        $renderer->render($blender, 'pdf', [])->willReturn('I am a nice blender!');

        $this->render($blender, 'pdf', [])->shouldReturn('I am a nice blender!');
    }

    function it_renders_a_document_with_the_renderer_which_has_the_higher_priority(
        RendererInterface $renderer,
        RendererInterface $betterRenderer,
        AbstractProduct $blender
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
        AbstractProduct $blender
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
        AbstractProduct $blender
    ) {
        $this->addRenderer($betterRenderer);
        $this->addRenderer($renderer);

        $renderer->supports($blender, 'pdf')->willReturn(false);
        $betterRenderer->supports($blender, 'pdf')->willReturn(false);

        $blender->__toString()->willReturn('I am a unrendered product');

        $this->shouldThrow('Pim\Bundle\PdfGeneratorBundle\Exception\RendererRequiredException')->during('render', [$blender, 'pdf', []]);
    }
}
