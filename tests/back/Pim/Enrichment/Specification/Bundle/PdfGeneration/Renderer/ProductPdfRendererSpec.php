<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
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
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $templating,
            $pdfBuilder,
            $dataManager,
            $cacheManager,
            $filterManager,
            $attributeRepository,
            self::TEMPLATE_NAME,
            '/tmp/'
        );
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
        ValueCollectionInterface $valueCollection,
        AttributeGroupInterface $design,
        AttributeInterface $color,
        $attributeRepository
    ) {
        $blender->getValues()->willReturn($valueCollection);
        $valueCollection->getAttributeCodes()->willReturn(['color']);

        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn('pim_catalog_text');

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imagePaths'        => [],
            'customFont'        => null,
            'optionLabels'      => [],
            'filter'            => 'pdf_thumbnail',
            'renderingDate'     => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }

    function it_renders_a_product_with_an_image(
        $templating,
        ProductInterface $blender,
        ValueCollectionInterface $valueCollection,
        AttributeGroupInterface $media,
        AttributeInterface $mainImage,
        ValueInterface $value,
        FileInfoInterface $fileInfo,
        CacheManager $cacheManager,
        $attributeRepository
    ) {
        $mainImage->isLocalizable()->willReturn(true);
        $mainImage->isScopable()->willReturn(true);

        $blender->getValues()->willReturn($valueCollection);
        $valueCollection->getAttributeCodes()->willReturn(['main_image']);
        $blender->getValue("main_image", "en_US", "ecommerce")->willReturn($value);

        $value->getData()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('fookey');

        $cacheManager->isStored('fookey', 'pdf_thumbnail')->willReturn(true);

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getType()->willReturn('pim_catalog_image');

        $attributeRepository->findOneByIdentifier('main_image')->willReturn($mainImage);

        $renderingDate = new \DateTime();

        $templating->render(
            self::TEMPLATE_NAME,
            [
                'product'           => $blender,
                'locale'            => 'en_US',
                'scope'             => 'ecommerce',
                'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
                'imagePaths'        => ['fookey'],
                'customFont'        => null,
                'optionLabels'      => [],
                'filter'            => 'pdf_thumbnail',
                'renderingDate'     => $renderingDate,
            ]
        )->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }
}
