<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Pdf;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\FilterProductValuesHelper;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
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
        IdentifiableObjectRepositoryInterface $attributeRepository,
        FilterProductValuesHelper $filterHelper,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        $uploadDirectory = realpath(__DIR__ . '/../../../../../features/Context/fixtures/');
        $this->beConstructedWith(
            $templating,
            $pdfBuilder,
            $filterHelper,
            $dataManager,
            $cacheManager,
            $filterManager,
            $attributeRepository,
            $channelRepository,
            $localeRepository,
            $assetRepository,
            self::TEMPLATE_NAME,
            $uploadDirectory,
            null
        );
    }

    function it_renders_a_product_without_images(
        $filterHelper,
        $templating,
        ProductInterface $blender,
        ArrayCollection $blenderValues,
        AttributeGroupInterface $design,
        AttributeInterface $color,
        ValueInterface $blue,
        $attributeRepository
    ) {
        $filterHelper->filter([$blue], 'en_US')->willReturn([$blue]);

        $blender->getValues()->willReturn($blenderValues);
        $blender->getUsedAttributeCodes()->willReturn(['color']);
        $blenderValues->toArray()->willReturn([$blue]);

        $blue->getAttributeCode()->willReturn('color');
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $color->getCode()->willReturn('color');

        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');

        $color->getType()->willReturn('pim_catalog_text');

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product' => $blender,
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imagePaths' => [],
            'customFont' => null,
            'filter' => 'pdf_thumbnail',
            'renderingDate' => $renderingDate,
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
        FileInfoInterface $fileInfo,
        $attributeRepository
    ) {
        $mainImage->isScopable()->willReturn(true);
        $mainImage->isLocalizable()->willReturn(true);

        $filterHelper->filter([$productValue], 'en_US')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getUsedAttributeCodes()->willReturn(['main_image']);
        $blender->getValue('main_image', 'en_US', 'ecommerce')->willReturn($productValue);

        $productValue->getAttributeCode()->willReturn('main_image');
        $attributeRepository->findOneByIdentifier('main_image')->willReturn($mainImage);
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
            'product' => $blender,
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'groupedAttributes' => ['Media' => ['main_image' => $mainImage]],
            'imagePaths' => ['fookey'],
            'customFont' => null,
            'filter' => 'pdf_thumbnail',
            'renderingDate' => $renderingDate,
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
        BinaryInterface $thumbnailFile,
        $attributeRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $filterHelper->filter([$productValue], 'fr_FR')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getUsedAttributeCodes()->willReturn(['front_view']);
        $blender->getValue('front_view', 'fr_FR', 'mobile')->willReturn($productValue);

        $productValue->getAttributeCode()->willReturn('front_view');

        $attributeRepository->findOneByIdentifier('front_view')->willReturn($assetCollectionAttr);

        $assetCollectionAttr->getCode()->willReturn('front_view');
        $assetCollectionAttr->getType()->willReturn('pim_assets_collection');
        $assetCollectionAttr->isScopable()->willReturn(true);
        $assetCollectionAttr->isLocalizable()->willReturn(true);

        $assetCollectionAttr->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $productValue->getData()->willReturn(['assetA', 'assetB']);
        $assetRepository->findOneByIdentifier('assetA')->willReturn($assetA);
        $assetRepository->findOneByIdentifier('assetB')->willReturn($assetB);

        $assetA->getFileForContext($channelMobile, $localeFr)->willReturn($fileInfoA);
        $assetB->getFileForContext($channelMobile, $localeFr)->willReturn($fileInfoB);

        $fileInfoA->getKey()->willReturn('fileA');
        $fileInfoA->getMimeType()->willReturn('image/jpeg');

        $fileInfoB->getKey()->willReturn('fileB');
        $fileInfoB->getMimeType()->willReturn('image/jpeg');

        $cacheManager->isStored('fileA', 'pdf_thumbnail')->willReturn(true);
        $cacheManager->isStored('fileB', 'pdf_thumbnail')->willReturn(false);

        $dataManager->find('pdf_thumbnail', 'fileB')->willReturn($srcFile);
        $filterManager->applyFilter($srcFile, 'pdf_thumbnail')->willReturn($thumbnailFile);
        $cacheManager->store($thumbnailFile, 'fileB', 'pdf_thumbnail')->shouldBeCalled();

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product' => $blender,
            'locale' => 'fr_FR',
            'scope' => 'mobile',
            'groupedAttributes' => ['Media' => ['front_view' => $assetCollectionAttr]],
            'imagePaths' => ['fileA', 'fileB'],
            'customFont' => null,
            'filter' => 'pdf_thumbnail',
            'renderingDate' => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'fr_FR', 'scope' => 'mobile', 'renderingDate' => $renderingDate]
        );
    }

    function it_does_not_render_a_product_with_an_asset_that_has_not_a_mimetype_of_type_image(
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
        FileInfoInterface $fileInfoImage,
        FileInfoInterface $fileInfoPdf,
        LocaleInterface $localeFr,
        ChannelInterface $channelMobile,
        BinaryInterface $srcFile,
        BinaryInterface $thumbnailFile,
        $attributeRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        $channelRepository->findOneByIdentifier('mobile')->willReturn($channelMobile);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $filterHelper->filter([$productValue], 'fr_FR')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blenderValues->toArray()->willReturn([$productValue]);

        $blender->getUsedAttributeCodes()->willReturn(['front_view']);
        $blender->getValue('front_view', 'fr_FR', 'mobile')->willReturn($productValue);

        $productValue->getAttributeCode()->willReturn('front_view');

        $attributeRepository->findOneByIdentifier('front_view')->willReturn($assetCollectionAttr);

        $assetCollectionAttr->getCode()->willReturn('front_view');
        $assetCollectionAttr->getType()->willReturn('pim_assets_collection');
        $assetCollectionAttr->isScopable()->willReturn(true);
        $assetCollectionAttr->isLocalizable()->willReturn(true);

        $assetCollectionAttr->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');

        $productValue->getData()->willReturn(['assetA', 'assetB']);
        $assetRepository->findOneByIdentifier('assetA')->willReturn($assetA);
        $assetRepository->findOneByIdentifier('assetB')->willReturn($assetB);

        $assetA->getFileForContext($channelMobile, $localeFr)->willReturn($fileInfoImage);
        $assetB->getFileForContext($channelMobile, $localeFr)->willReturn($fileInfoPdf);

        $fileInfoImage->getKey()->willReturn('image_file');
        $fileInfoImage->getMimeType()->willReturn('image/jpeg');

        $fileInfoPdf->getKey()->willReturn('pdf_file');
        $fileInfoPdf->getMimeType()->willReturn('application/pdf');

        $cacheManager->isStored('image_file', 'pdf_thumbnail')->willReturn(true);
        $cacheManager->isStored('pdf_file', 'pdf_thumbnail')->willReturn(true);

        $dataManager->find('pdf_thumbnail', 'pdf')->willReturn($srcFile);
        $filterManager->applyFilter($srcFile, 'pdf_thumbnail')->willReturn($thumbnailFile);
        $cacheManager->store($thumbnailFile, 'pdf', 'pdf_thumbnail')->shouldNotBeCalled();

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product' => $blender,
            'locale' => 'fr_FR',
            'scope' => 'mobile',
            'groupedAttributes' => ['Media' => ['front_view' => $assetCollectionAttr]],
            'imagePaths' => ['image_file'],
            'customFont' => null,
            'filter' => 'pdf_thumbnail',
            'renderingDate' => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'fr_FR', 'scope' => 'mobile', 'renderingDate' => $renderingDate]
        );
    }
}
