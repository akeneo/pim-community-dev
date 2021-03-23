<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Pdf;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\FilterProductValuesHelper;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class ProductPdfRendererSpec extends ObjectBehavior
{
    const TEMPLATE_NAME = 'template.html.twig';

    function let(
        Environment $templating,
        PdfBuilderInterface $pdfBuilder,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        FilterProductValuesHelper $filterHelper,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
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
            self::TEMPLATE_NAME,
            $attributeOptionRepository,
            $authorizationChecker,
            null,
            $authorizationChecker
        );
    }

    function it_renders_a_product_without_images(
        $filterHelper,
        $templating,
        ProductInterface $blender,
        WriteValueCollection $blenderValues,
        AttributeGroupInterface $design,
        AttributeInterface $color,
        ValueInterface $blue,
        FamilyInterface $family,
        $attributeRepository,
        $authorizationChecker
    ) {
        $filterHelper->filter([$blue], 'en_US')->willReturn([$blue]);

        $blender->getValues()->willReturn($blenderValues);
        $blender->getUsedAttributeCodes()->willReturn(['color']);
        $blenderValues->getAttributeCodes()->willReturn(['color']);
        $blenderValues->toArray()->willReturn([$blue]);
        $blender->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['color']);

        $blue->getAttributeCode()->willReturn('color');
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);

        $color->getCode()->willReturn('color');
        $color->getGroup()->willReturn($design);
        $color->getType()->willReturn('pim_catalog_text');
        $color->isScopable()->willReturn(false);
        $color->isLocalizable()->willReturn(false);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $design)->willReturn(true);

        $design->getLabel()->willReturn('Design');

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product' => $blender,
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imagePaths' => [],
            'customFont' => null,
            'optionLabels' => [],
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
        WriteValueCollection $blenderValues,
        AttributeGroupInterface $media,
        AttributeInterface $mainImage,
        ValueInterface $productValue,
        FileInfoInterface $fileInfo,
        FamilyInterface $family,
        $attributeRepository,
        $authorizationChecker
    ) {
        $mainImage->isScopable()->willReturn(true);
        $mainImage->isLocalizable()->willReturn(true);

        $filterHelper->filter([$productValue], 'en_US')->willReturn([$productValue]);
        $blender->getValues()->willReturn($blenderValues);
        $blender->getUsedAttributeCodes()->willReturn(['main_image']);
        $blenderValues->toArray()->willReturn([$productValue]);
        $blender->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['main_image']);

        $blenderValues->getAttributeCodes()->willReturn(['main_image']);
        $blender->getValue('main_image', 'en_US', 'ecommerce')->willReturn($productValue);

        $productValue->getAttributeCode()->willReturn('main_image');
        $attributeRepository->findOneByIdentifier('main_image')->willReturn($mainImage);
        $productValue->getData()->willReturn($fileInfo);

        $mainImage->getGroup()->willReturn($media);
        $mainImage->getCode()->willReturn('main_image');
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $media)->willReturn(true);

        $mainImage->getGroup()->willReturn($media);
        $media->getLabel()->willReturn('Media');
        $mainImage->getType()->willReturn('pim_catalog_image');

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
            'optionLabels' => [],
            'filter' => 'pdf_thumbnail',
            'renderingDate' => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }

    function it_does_not_render_inherited_attributes_if_no_rights_given(
        $filterHelper,
        $templating,
        ProductInterface $blender,
        WriteValueCollection $blenderValues,
        AttributeGroupInterface $design,
        AttributeGroupInterface $marketing,
        AttributeInterface $color,
        AttributeInterface $name,
        ValueInterface $blue,
        FamilyInterface $family,
        $attributeRepository,
        $authorizationChecker
    ) {
        $filterHelper->filter([$blue], 'en_US')->willReturn([$blue]);

        $blender->getValues()->willReturn($blenderValues);
        $blender->getUsedAttributeCodes()->willReturn(['color']);
        $blenderValues->getAttributeCodes()->willReturn(['color']);
        $blenderValues->toArray()->willReturn([$blue]);
        $blender->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['color', 'name']);

        $blue->getAttributeCode()->willReturn('color');
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);

        $color->getCode()->willReturn('color');
        $color->getGroup()->willReturn($design);
        $color->getType()->willReturn('pim_catalog_text');
        $color->isScopable()->willReturn(false);
        $color->isLocalizable()->willReturn(false);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $design)->willReturn(true);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $name->getGroup()->willReturn($marketing);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $marketing)->willReturn(false);

        $design->getLabel()->willReturn('Design');

        $renderingDate = new \DateTime();

        $templating->render(self::TEMPLATE_NAME, [
            'product' => $blender,
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'groupedAttributes' => ['Design' => ['color' => $color]],
            'imagePaths' => [],
            'customFont' => null,
            'optionLabels' => [],
            'filter' => 'pdf_thumbnail',
            'renderingDate' => $renderingDate,
        ])->shouldBeCalled();

        $this->render(
            $blender,
            'plain',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }
}
