<?php

namespace spec\PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
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
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        FilterProductValuesHelper $filterHelper
    ) {
        $uploadDirectory = realpath(__DIR__ . '/../../../../../features/Context/fixtures/');
        $this->beConstructedWith(
            $templating,
            $pdfBuilder,
            $filterHelper,
            $dataManager,
            $cacheManager,
            $filterManager,
            self::TEMPLATE_NAME,
            $uploadDirectory
        );
    }

    function it_renders_a_product_without_images(
        $filterHelper,
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $design,
        AttributeInterface $color,
        ProductValueInterface $blue,
        $templating
    ) {
        $uploadDirectory = realpath(__DIR__ . '/../../../../../features/Context/fixtures/');
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
            'imageAttributes'   => [],
            'uploadDir'         => $uploadDirectory . DIRECTORY_SEPARATOR,
            'customFont'        => null
        ])->shouldBeCalled();

        $this->render($blender, 'plain', ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }

    function it_renders_a_product_with_an_image(
        $filterHelper,
        ProductInterface $blender,
        AttributeGroupInterface $media,
        AttributeInterface $mainImage,
        ProductValueInterface $productValue,
        ArrayCollection $blenderValues,
        FileInfoInterface $fileInfo,
        CacheManager $cacheManager,
        $templating
    ) {
        $uploadDirectory = realpath(__DIR__ . '/../../../../../features/Context/fixtures/');

        $filterHelper->filter([$productValue], 'en_US')->willReturn([$productValue]);

        $blender->getAttributes()->willReturn($mainImage);
        $blender->getValue("main_image", "en_US", "ecommerce")->willReturn($productValue);
        $blender->getValues()->willReturn($blenderValues);

        $blenderValues->toArray()->willReturn([$productValue]);

        $productValue->getAttribute()->willReturn($mainImage);
        $productValue->getMedia()->willReturn($fileInfo);

        $mainImage->getGroup()->willReturn($media);
        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getAttributeType()->willReturn('pim_catalog_image');

        $fileInfo->getKey()->willReturn('fookey');

        $cacheManager->isStored('fookey', 'thumbnail')->willReturn(true);

        $media->getLabel()->willReturn('Media');

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
            'imageAttributes'   => ['main_image' => $mainImage],
            'uploadDir'         => $uploadDirectory . DIRECTORY_SEPARATOR,
            'customFont'        => null
        ])->shouldBeCalled();

        $this->render($blender, 'pdf', ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }
}
