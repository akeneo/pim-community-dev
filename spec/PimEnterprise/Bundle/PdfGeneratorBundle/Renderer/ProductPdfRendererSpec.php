<?php

namespace spec\PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
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
        FilterProductValuesHelper $filterHelper,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $uploadDirectory = realpath(__DIR__ . '/../../../../../features/Context/fixtures/');
        $this->beConstructedWith(
            $templating,
            $pdfBuilder,
            $filterHelper,
            $dataManager,
            $cacheManager,
            $filterManager,
            $channelRepository,
            $localeRepository,
            self::TEMPLATE_NAME,
            $uploadDirectory
        );
    }

    function it_renders_a_product_without_images(
        $filterHelper,
        $templating,
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $design,
        AttributeInterface $color,
        ValueInterface $blue
    ) {
        $filterHelper->filter([$blue], 'en_US')->willReturn([$blue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$blue]);

        $blue->getAttribute()->willReturn($color);
        $color->getCode()->willReturn('color');

        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');

        $color->getType()->willReturn('pim_catalog_text');

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imagePaths'        => [],
            'customFont'        => null,
            'filter'            => 'pdf_thumbnail',
            'renderingDate'     => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'plain',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }

    function it_renders_a_product_with_an_image(
        $filterHelper,
        $templating,
        $cacheManager,
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $media,
        AttributeInterface $mainImage,
        ValueInterface $productValue,
        FileInfoInterface $fileInfo
    ) {
        $mainImage->isScopable()->willReturn(true);
        $mainImage->isLocalizable()->willReturn(true);

        $filterHelper->filter([$productValue], 'en_US')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getAttributes()->willReturn([$mainImage]);
        $blender->getValue('main_image', 'en_US', 'ecommerce')->willReturn($productValue);

        $productValue->getAttribute()->willReturn($mainImage);
        $productValue->getData()->willReturn($fileInfo);

        $mainImage->getGroup()->willReturn($media);
        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getType()->willReturn('pim_catalog_image');

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $productValue->getData()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('fookey');

        $cacheManager->isStored('fookey', 'pdf_thumbnail')->willReturn(true);

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
            'imagePaths'        => ['fookey'],
            'customFont'        => null,
            'filter'            => 'pdf_thumbnail',
            'renderingDate'     => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }

    function it_renders_a_product_with_assets(
        $filterHelper,
        $templating,
        $dataManager,
        $filterManager,
        $cacheManager,
        $channelRepository,
        $localeRepository,
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $media,
        AttributeInterface $assetCollectionAttr,
        ValueInterface $productValue,
        AssetInterface $assetA,
        AssetInterface $assetB,
        FileInfoInterface $fileInfoA,
        FileInfoInterface $fileInfoB,
        LocaleInterface $localeFr,
        ChannelInterface $channelMobile,
        BinaryInterface $srcFile,
        BinaryInterface $thumbnailFile
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $filterHelper->filter([$productValue], 'fr_FR')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getAttributes()->willReturn([$assetCollectionAttr]);
        $blender->getValue('front_view', 'fr_FR', 'mobile')->willReturn($productValue);

        $productValue->getAttribute()->willReturn($assetCollectionAttr);
        $assetCollectionAttr->getCode()->willReturn('front_view');
        $assetCollectionAttr->getType()->willReturn('pim_assets_collection');
        $assetCollectionAttr->isScopable()->willReturn(true);
        $assetCollectionAttr->isLocalizable()->willReturn(true);

        $assetCollectionAttr->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $productValue->getData()->willReturn([$assetA, $assetB]);
        $assetA->getFileForContext($channelMobile, $localeFr)->willReturn($fileInfoA);
        $assetB->getFileForContext($channelMobile, $localeFr)->willReturn($fileInfoB);

        $fileInfoA->getKey()->willReturn('fileA');
        $fileInfoB->getKey()->willReturn('fileB');

        $cacheManager->isStored('fileA', 'pdf_thumbnail')->willReturn(true);
        $cacheManager->isStored('fileB', 'pdf_thumbnail')->willReturn(false);

        $dataManager->find('pdf_thumbnail', 'fileB')->willReturn($srcFile);
        $filterManager->applyFilter($srcFile, 'pdf_thumbnail')->willReturn($thumbnailFile);
        $cacheManager->store($thumbnailFile, 'fileB', 'pdf_thumbnail')->shouldBeCalled();

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'fr_FR',
            'scope'             => 'mobile',
            'groupedAttributes' => ['Media' => ['front_view' => $assetCollectionAttr]],
            'imagePaths'        => ['fileA', 'fileB'],
            'customFont'        => null,
            'filter'            => 'pdf_thumbnail',
            'renderingDate'     => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'fr_FR', 'scope' => 'mobile', 'renderingDate' => $renderingDate]
        );
    }
}
