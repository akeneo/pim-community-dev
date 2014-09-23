<?php

namespace spec\PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ProductPdfRendererSpec extends ObjectBehavior
{
    const TEMPLATE_NAME = 'template.html.twig';

    function let(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper
    ) {
        $this->beConstructedWith($templating, self::TEMPLATE_NAME, $pdfBuilder, $filterHelper);
    }

    function it_renders_a_product_without_images(
        $filterHelper,
        AbstractProduct $blender,
        ArrayCollection $blenderValues,
        AttributeGroup $design,
        AbstractAttribute $color,
        AbstractProductValue $blue,
        $templating
    ) {
        $filterHelper->filter([$blue], 'en_US')->willReturn([$blue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$blue]);
        $blue->getAttribute()->willReturn($color);
        $color->getCode()->willReturn('color');

        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');

        $color->getAttributeType()->willReturn('pim_catalog_text');

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imageAttributes'   => []
        ])->shouldBeCalled();

        $this->render($blender, 'plain', ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }

    function it_renders_a_product_with_an_image(
        $filterHelper,
        AbstractProduct $blender,
        ArrayCollection $blenderValues,
        AttributeGroup $media,
        AbstractAttribute $mainImage,
        AbstractProductValue $blenderPicture,
        $templating
    ) {
        $filterHelper->filter([$blenderPicture], 'en_US')->willReturn([$blenderPicture]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$blenderPicture]);
        $blenderPicture->getAttribute()->willReturn($mainImage);
        $mainImage->getCode()->willReturn('main_image');

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getAttributeType()->willReturn('pim_catalog_image');

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
            'imageAttributes'   => ['main_image' => $mainImage]
        ])->shouldBeCalled();

        $this->render($blender, 'plain', ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }
}
