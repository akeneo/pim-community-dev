<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithFamilyVariantNormalizerSpec extends ObjectBehavior
{
    function let(
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        NormalizerInterface $completenessCollectionNormalizer,
        CompletenessCalculatorInterface $completenessCalculator,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        CatalogContext $catalogContext,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        GetProductCompletenesses $getProductCompletenesses,
        AxisValueLabelsNormalizer $simpleSelectOptionNormalizer,
        AxisValueLabelsNormalizer $metricNormalizer
    ) {
        $this->beConstructedWith(
            $imageNormalizer,
            $localeRepository,
            $attributesProvider,
            $completenessCollectionNormalizer,
            $completenessCalculator,
            $variantProductRatioQuery,
            $imageAsLabel,
            $catalogContext,
            $attributeOptionRepository,
            $getProductCompletenesses,
            $simpleSelectOptionNormalizer,
            $metricNormalizer
        );
    }

    function it_throws_an_exception_if_the_entity_is_not_a_variant_product_nor_a_product_model(
        \stdClass $entity
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'normalize', [$entity, 'internal_api']
        );
    }

    function it_normalizes_a_variant_product(
        $localeRepository,
        $attributesProvider,
        $completenessCollectionNormalizer,
        $completenessCalculator,
        $getProductCompletenesses,
        ProductInterface $variantProduct,
        AttributeInterface $colorAttribute,
        AttributeInterface $sizeAttribute,
        AttributeInterface $weightAttribute,
        ValueInterface $colorValue,
        ValueInterface $sizeValue,
        MetricValueInterface $weightValue,
        MetricInterface $weightData,
        AttributeOptionInterface $colorAttributeOption,
        AttributeOptionValueInterface $colorAttributeOptionValue,
        CompletenessInterface $completeness1,
        CompletenessInterface $completeness2,
        $attributeOptionRepository,
        $simpleSelectOptionNormalizer,
        $metricNormalizer
    ) {
        $context = [
            'locale' => 'en_US'
        ];
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getLabel('fr_FR')->willReturn('Tshirt Blanc S');
        $variantProduct->getLabel('en_US')->willReturn('Tshirt White S');
        $variantProduct->getId()->willReturn(42);

        $variantProduct->getIdentifier()->willReturn('tshirt_white_s');

        $attributesProvider->getAxes($variantProduct)->willReturn([
            $colorAttribute,
            $sizeAttribute,
            $weightAttribute,
        ]);

        $colorAttribute->getCode()->willReturn('color');
        $colorAttribute->getType()->willReturn('pim_catalog_simpleselect');
        $sizeAttribute->getCode()->willReturn('size');
        $sizeAttribute->getType()->willReturn('pim_catalog_text');
        $weightAttribute->getCode()->willReturn('weight');
        $weightAttribute->getType()->willReturn('pim_catalog_metric');
        $variantProduct->getValue('color')->willReturn($colorValue);
        $variantProduct->getValue('size')->willReturn($sizeValue);
        $variantProduct->getValue('weight')->willReturn($weightValue);
        $weightValue->getData()->willReturn($weightData);;
        $weightValue->getAmount()->willReturn(10);
        $weightValue->getUnit()->willReturn('KILOGRAM');
        $weightData->getUnit()->willReturn('KILOGRAM');
        $weightData->getData()->willReturn(10);

        $colorValue->getData()->willReturn('white');
        $colorValue->getAttributeCode()->willReturn('color');

        $attributeOptionRepository->findOneByIdentifier('color.white')->willReturn($colorAttributeOption);

        $colorAttributeOption->getSortOrder()->willReturn(2);
        $colorAttributeOption->getCode()->willReturn('white');
        $colorAttributeOption->getTranslation()->willReturn($colorAttributeOptionValue);
        $colorAttributeOptionValue->getLabel()->willReturn('Blanc', 'White');
        $sizeValue->__toString()->willReturn('S');
        $colorValue->__toString()->willReturn('Blanc default', 'White default');
        $weightValue->__toString()->willReturn('10 KILOGRAM default');

        $variantProduct->getImage()->willReturn(null);

        $getProductCompletenesses->fromProductId(42)->willReturn([]);
        $completenessCollectionNormalizer->normalize([], 'internal_api')->willReturn(['NORMALIZED_COMPLETENESS']);

        $completenessCalculator->calculate($variantProduct)->willReturn($completeness1, $completeness2);

        $simpleSelectOptionNormalizer->supports(Argument::any())->willReturn(false);
        $simpleSelectOptionNormalizer->supports('pim_catalog_simpleselect')->willReturn(true);
        $simpleSelectOptionNormalizer->normalize($colorValue, 'fr_FR')->willReturn('Blanc');
        $simpleSelectOptionNormalizer->normalize($colorValue, 'en_US')->willReturn('White');

        $metricNormalizer->supports(Argument::any())->willReturn(false);
        $metricNormalizer->supports('pim_catalog_metric')->willReturn(true);
        $metricNormalizer->normalize($weightValue, 'fr_FR')->willReturn('10 KILOGRAM');
        $metricNormalizer->normalize($weightValue, 'en_US')->willReturn('10 KILOGRAM');

        $this->normalize($variantProduct, 'internal_api', $context)->shouldReturn([
            'id'                 => 42,
            'identifier'         => 'tshirt_white_s',
            'axes_values_labels' => [
                'fr_FR' => 'Blanc, S, 10 KILOGRAM',
                'en_US' => 'White, S, 10 KILOGRAM',
            ],
            'labels'             => [
                'fr_FR' => 'Tshirt Blanc S',
                'en_US' => 'Tshirt White S',
            ],
            'order'              => [2, 'white', 'S', 'KILOGRAM', 10.0],
            'image'              => null,
            'model_type'         => 'product',
            'completeness'       => ['NORMALIZED_COMPLETENESS']
        ]);
    }

    function it_normalizes_a_product_model(
        $localeRepository,
        $attributesProvider,
        $variantProductRatioQuery,
        ProductModelInterface $productModel,
        AttributeInterface $colorAttribute,
        ValueInterface $colorValue,
        AttributeOptionInterface $colorAttributeOption,
        AttributeOptionValueInterface $colorAttributeOptionValue,
        CompleteVariantProducts $completeVariantProducts,
        $attributeOptionRepository,
        $simpleSelectOptionNormalizer
    ) {
        $context = [
            'locale' => 'en_US'
        ];
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);

        $productModel->getLabel('fr_FR')->willReturn('Tshirt Blanc');
        $productModel->getLabel('en_US')->willReturn('Tshirt White');
        $productModel->getId()->willReturn(5);

        $productModel->getCode()->willReturn('tshirt_white');

        $attributesProvider->getAxes($productModel)->willReturn([$colorAttribute]);

        $colorAttribute->getCode()->willReturn('color');
        $colorAttribute->getType()->willReturn('pim_catalog_simpleselect');
        $productModel->getValue('color')->willReturn($colorValue);

        $colorValue->getData()->willReturn('white');
        $colorValue->getAttributeCode()->willReturn('color');
        $colorValue->__toString()->willReturn('Blanc', 'White');

        $attributeOptionRepository->findOneByIdentifier('color.white')->willReturn($colorAttributeOption);
        $colorAttributeOption->getSortOrder()->willReturn(2);
        $colorAttributeOption->getCode()->willReturn('white');
        $colorAttributeOption->getTranslation()->willReturn($colorAttributeOptionValue);
        $colorAttributeOptionValue->getLabel()->willReturn('Blanc default', 'White default');

        $variantProductRatioQuery->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn(['NORMALIZED COMPLETENESS']);

        $simpleSelectOptionNormalizer->supports('pim_catalog_simpleselect')->willReturn(true);
        $simpleSelectOptionNormalizer->normalize($colorValue, 'fr_FR')->willReturn('Blanc');
        $simpleSelectOptionNormalizer->normalize($colorValue, 'en_US')->willReturn('White');

        $this->normalize($productModel, 'internal_api', $context)->shouldReturn([
            'id'                 => 5,
            'identifier'         => 'tshirt_white',
            'axes_values_labels' => [
                'fr_FR' => 'Blanc',
                'en_US' => 'White',
            ],
            'labels'             => [
                'fr_FR' => 'Tshirt Blanc',
                'en_US' => 'Tshirt White',
            ],
            'order'              => [2, 'white'],
            'image'              => null,
            'model_type'         => 'product_model',
            'completeness'       => ['NORMALIZED COMPLETENESS']
        ]);
    }
}
