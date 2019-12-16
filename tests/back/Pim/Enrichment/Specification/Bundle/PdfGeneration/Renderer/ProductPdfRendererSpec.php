<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
        IdentifiableObjectRepositoryInterface $attributeOptionRepository
    ) {
        $this->beConstructedWith(
            $templating,
            $pdfBuilder,
            $dataManager,
            $cacheManager,
            $filterManager,
            $attributeRepository,
            self::TEMPLATE_NAME,
            $attributeOptionRepository,
            null
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
        AttributeGroupInterface $design,
        AttributeInterface $color,
        $attributeRepository
    ) {
        $blender->getUsedAttributeCodes()->willReturn(['color']);

        $color->getGroup()->willReturn($design);
        $design->getLabel()->willReturn('Design');
        $color->getCode()->willReturn('color');
        $color->getType()->willReturn('pim_catalog_text');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);

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
        AttributeGroupInterface $media,
        AttributeInterface $mainImage,
        ValueInterface $value,
        FileInfoInterface $fileInfo,
        CacheManager $cacheManager,
        $attributeRepository
    ) {
        $mainImage->isLocalizable()->willReturn(true);
        $mainImage->isScopable()->willReturn(true);

        $blender->getUsedAttributeCodes()->willReturn(['main_image']);
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

    function it_renders_options_labels(
        EngineInterface $templating,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository
    ) {
        $colors = new Attribute();
        $colors->setCode('colors');
        $colors->setType(AttributeTypes::OPTION_MULTI_SELECT);
        $colors->setLocale('en_US');
        $colors->setLabel('Colors');
        $group = new AttributeGroup();
        $group->setLocale('en_US');
        $group->setLabel('Marketing');
        $colors->setGroup($group);
        $colors->setLocalizable(true);
        $colors->setScopable(true);
        $attributeRepository->findOneByIdentifier('colors')->willReturn($colors);

        $blue = new AttributeOption();
        $blue->setCode('blue');
        $blueValue = new AttributeOptionValue();
        $blueValue->setLocale('en_US');
        $blueValue->setLabel('Blue');
        $blue->addOptionValue($blueValue);
        $attributeOptionRepository->findOneByIdentifier('colors.blue')->willReturn($blue);

        $red = new AttributeOption();
        $red->setCode('red');
        $redValue = new AttributeOptionValue();
        $redValue->setLocale('en_US');
        $redValue->setLabel('Red');
        $red->addOptionValue($redValue);
        $attributeOptionRepository->findOneByIdentifier('colors.red')->willReturn($red);

        $product = new Product();
        $product->setValues(
            new WriteValueCollection(
                [
                    OptionsValue::scopableLocalizableValue('colors', ['blue', 'red'], 'ecommerce', 'en_US'),
                    OptionsValue::scopableLocalizableValue('colors', ['red'], 'ecommerce', 'fr_FR'),
                ]
            )
        );

        $renderingDate = new \DateTime();
        $templating->render(
            self::TEMPLATE_NAME,
            [
                'product' => $product,
                'locale' => 'en_US',
                'scope' => 'ecommerce',
                'groupedAttributes' => ['Marketing' => ['colors' => $colors]],
                'imagePaths' => [],
                'customFont' => null,
                'optionLabels' => ['colors' => 'Blue, Red'],
                'filter' => 'pdf_thumbnail',
                'renderingDate' => $renderingDate,
            ]
        )->shouldBeCalled();

        $this->render(
            $product,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }

    function it_renders_a_simple_select_without_any_option_for_a_given_locale(
        EngineInterface $templating,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository
    ) {
        $colors = new Attribute();
        $colors->setCode('colors');
        $colors->setType(AttributeTypes::OPTION_SIMPLE_SELECT);
        $colors->setLocale('en_US');
        $colors->setLabel('Colors');
        $group = new AttributeGroup();
        $group->setLocale('en_US');
        $group->setLabel('Marketing');
        $colors->setGroup($group);
        $colors->setLocalizable(true);
        $colors->setScopable(true);
        $attributeRepository->findOneByIdentifier('colors')->willReturn($colors);

        $product = new Product();
        $product->setValues(
            new WriteValueCollection(
                [
                    OptionsValue::scopableLocalizableValue('colors', [], 'ecommerce', 'fr_FR'),
                ]
            )
        );

        $renderingDate = new \DateTime();
        $templating->render(
            self::TEMPLATE_NAME,
            [
                'product' => $product,
                'locale' => 'en_US',
                'scope' => 'ecommerce',
                'groupedAttributes' => ['Marketing' => ['colors' => $colors]],
                'imagePaths' => [],
                'customFont' => null,
                'optionLabels' => [],
                'filter' => 'pdf_thumbnail',
                'renderingDate' => $renderingDate,
            ]
        )->shouldBeCalled();

        $this->render(
            $product,
            'pdf',
            ['locale' => 'en_US', 'scope' => 'ecommerce', 'renderingDate' => $renderingDate]
        );
    }
}
