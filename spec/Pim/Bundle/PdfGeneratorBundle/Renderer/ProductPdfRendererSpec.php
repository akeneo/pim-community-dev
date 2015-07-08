<?php

namespace spec\Pim\Bundle\PdfGeneratorBundle\Renderer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ProductPdfRendererSpec extends ObjectBehavior
{
    const TEMPLATE_NAME = 'template.html.twig';

    function let(EngineInterface $templating, PdfBuilderInterface $pdfBuilder)
    {
        $path = realpath(__DIR__.'/../../../../../features/Context/fixtures/');
        $this->beConstructedWith($templating, self::TEMPLATE_NAME, $pdfBuilder, $path);
    }

    function it_does_not_filter_compatible_entities(ProductInterface $blender)
    {
        $this->supports($blender, 'pdf')->shouldReturn(true);
    }

    function it_filters_not_compatible_entities(Category $printer)
    {
        $this->supports($printer, 'pdf')->shouldReturn(false);
    }

    function it_renders_a_product_without_images(
        $templating,
        ProductInterface $blender,
        AttributeGroupInterface $design,
        AttributeInterface $color
    ) {
        $blender->getAttributes()->willReturn([$color]);

        $path = realpath(__DIR__.'/../../../../../features/Context/fixtures/');
        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');

        $color->getCode()->willReturn('color');
        $color->getAttributeType()->willReturn('pim_catalog_text');

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imageAttributes'   => [],
            'uploadDir'         => $path . DIRECTORY_SEPARATOR,
            'customFont'        => null
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );
    }

    function it_renders_a_product_with_an_image(
        $templating,
        ProductInterface $blender,
        AttributeGroupInterface $media,
        AttributeInterface $mainImage
    ) {
        $path = realpath(__DIR__ . '/../../../../../features/Context/fixtures/');
        $blender->getAttributes()->willReturn([$mainImage]);

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getAttributeType()->willReturn('pim_catalog_image');

        $templating->render(
            self::TEMPLATE_NAME,
            [
                'product'           => $blender,
                'locale'            => 'en_US',
                'scope'             => 'ecommerce',
                'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
                'imageAttributes'   => ['main_image' => $mainImage],
                'uploadDir'         => $path . DIRECTORY_SEPARATOR,
                'customFont'        => null
            ]
        )->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );
    }
}
