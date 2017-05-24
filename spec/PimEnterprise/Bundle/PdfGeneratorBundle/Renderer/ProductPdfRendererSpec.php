<?php

namespace spec\PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use PimEnterprise\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
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
        $templating,
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $design,
        AttributeInterface $color,
        ProductValueInterface $blue
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
            'filter'            => 'thumbnail',
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
        ProductValueInterface $productValue,
        FileInfoInterface $fileInfo
    ) {
        $filterHelper->filter([$productValue], 'en_US')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getAttributes()->willReturn([$mainImage]);
        $blender->getValue('main_image', 'en_US', 'ecommerce')->willReturn($productValue);

        $productValue->getAttribute()->willReturn($mainImage);
        $mainImage->getCode()->willReturn('main_image');
        $mainImage->getType()->willReturn('pim_catalog_image');

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $productValue->getMedia()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('fookey');

        $cacheManager->isStored('fookey', 'thumbnail')->willReturn(true);

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'en_US',
            'scope'             => 'ecommerce',
            'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
            'imagePaths'        => ['fookey'],
            'customFont'        => null,
            'filter'            => 'thumbnail',
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
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $media,
        AttributeInterface $assetCollectionAttr,
        ProductValueInterface $productValue,
        AssetInterface $assetA,
        AssetInterface $assetB,
        ReferenceInterface $refAEn,
        ReferenceInterface $refAFr,
        ReferenceInterface $refB,
        VariationInterface $variationAFrEcommerce,
        VariationInterface $variationAFrMobile,
        VariationInterface $variationBEcommerce,
        VariationInterface $variationBMobile,
        FileInfoInterface $fileInfoA,
        FileInfoInterface $fileInfoB,
        LocaleInterface $localeEn,
        LocaleInterface $localeFr,
        ChannelInterface $channelEcommerce,
        ChannelInterface $channelMobile,
        BinaryInterface $srcFile,
        BinaryInterface $thumbnailFile
    ) {
        $filterHelper->filter([$productValue], 'fr_FR')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getAttributes()->willReturn([$assetCollectionAttr]);
        $blender->getValue('front_view', 'fr_FR', 'mobile')->willReturn($productValue);

        $productValue->getAttribute()->willReturn($assetCollectionAttr);
        $assetCollectionAttr->getCode()->willReturn('front_view');
        $assetCollectionAttr->getType()->willReturn('pim_assets_collection');

        $assetCollectionAttr->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $productValue->getAssets()->willReturn([$assetA, $assetB]);
        $assetA->isLocalizable()->willReturn(true);
        $assetB->isLocalizable()->willReturn(false);
        $assetA->getReferences()->willReturn([$refAEn, $refAFr]);
        $assetB->getReferences()->willReturn([$refB]);
        $refAEn->getLocale()->willReturn($localeEn);
        $refAFr->getLocale()->willReturn($localeFr);

        $refAFr->getVariations()->willReturn([$variationAFrEcommerce, $variationAFrMobile]);
        $variationAFrEcommerce->getChannel()->willReturn($channelEcommerce);
        $variationAFrMobile->getChannel()->willReturn($channelMobile);
        $variationAFrMobile->getFileInfo()->willReturn($fileInfoA);

        $refB->getVariations()->willReturn([$variationBEcommerce, $variationBMobile]);
        $variationBEcommerce->getChannel()->willReturn($channelEcommerce);
        $variationBMobile->getChannel()->willReturn($channelMobile);
        $variationBMobile->getFileInfo()->willReturn($fileInfoB);

        $localeEn->getCode()->willReturn('en_US');
        $localeFr->getCode()->willReturn('fr_FR');
        $channelEcommerce->getCode()->willReturn('ecommerce');
        $channelMobile->getCode()->willReturn('mobile');

        $fileInfoA->getKey()->willReturn('fileA');
        $fileInfoB->getKey()->willReturn('fileB');

        $cacheManager->isStored('fileA', 'thumbnail')->willReturn(true);
        $cacheManager->isStored('fileB', 'thumbnail')->willReturn(false);

        $dataManager->find('thumbnail', 'fileB')->willReturn($srcFile);
        $filterManager->applyFilter($srcFile, 'thumbnail')->willReturn($thumbnailFile);
        $cacheManager->store($thumbnailFile, 'fileB', 'thumbnail')->shouldBeCalled();

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product'           => $blender,
            'locale'            => 'fr_FR',
            'scope'             => 'mobile',
            'groupedAttributes' => ['Media' => ['front_view' => $assetCollectionAttr]],
            'imagePaths'        => ['fileA', 'fileB'],
            'customFont'        => null,
            'filter'            => 'thumbnail',
            'renderingDate'     => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'fr_FR', 'scope' => 'mobile', 'renderingDate' => $renderingDate]
        );
    }
}
