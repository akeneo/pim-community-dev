<?php

namespace spec\Pim\Bundle\PdfGeneratorBundle\Renderer;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductPdfRendererSpec extends ObjectBehavior
{
    const TEMPLATE_NAME = 'template.html.twig';

    function let(EngineInterface $templating, PdfBuilderInterface $pdfBuilder)
    {
        $this->beConstructedWith($templating, self::TEMPLATE_NAME, $pdfBuilder);
    }

    function it_does_not_filter_compatible_entities(AbstractProduct $blender)
    {
        $this->supports($blender, 'plain')->shouldReturn(true);
    }

    function it_filters_not_compatible_entities(Category $printer)
    {
        $this->supports($printer, 'plain')->shouldReturn(false);
    }

    function it_renders_a_product_without_images(AbstractProduct $blender, AttributeGroup $design, AbstractAttribute $color, $templating)
    {
        $blender->getAttributes()->willReturn([$color]);

        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');

        $color->getCode()->willReturn('color');
        $color->getAttributeType()->willReturn('pim_catalog_text');

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imageAttributes'   => []
        ])->shouldBeCalled();

        $this->render($blender, 'plain', ['locale' => 'en_US']);
    }

    function it_renders_a_product_with_an_image(AbstractProduct $blender, AttributeGroup $media, AbstractAttribute $mainImage, $templating)
    {
        $blender->getAttributes()->willReturn([$mainImage]);

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getAttributeType()->willReturn('pim_catalog_image');

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
            'imageAttributes'   => ['main_image' => $mainImage]
        ])->shouldBeCalled();

        $this->render($blender, 'plain', ['locale' => 'en_US']);
    }
}
