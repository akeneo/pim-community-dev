<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessWithMissingAttributeCodesCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EntityWithFamilyVariantNormalizerSpec extends ObjectBehavior
{
    function let(
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        CatalogContext $catalogContext,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        CompletenessCalculator $completenessCalculator,
        AxisValueLabelsNormalizer $simpleSelectOptionNormalizer,
        AxisValueLabelsNormalizer $metricNormalizer,
        AxisValueLabelsNormalizer $booleanNormalizer
    ) {
        $simpleSelectOptionNormalizer->supports(Argument::type('string'))->will(
            function ($arguments): bool {
                return AttributeTypes::OPTION_SIMPLE_SELECT === $arguments[0];
            }
        );
        $metricNormalizer->supports(Argument::type('string'))->will(
            function ($arguments): bool {
                return AttributeTypes::METRIC === $arguments[0];
            }
        );
        $booleanNormalizer->supports(Argument::type('string'))->will(
            function ($arguments): bool {
                return AttributeTypes::BOOLEAN === $arguments[0];
            }
        );

        $this->beConstructedWith(
            $imageNormalizer,
            $localeRepository,
            $attributesProvider,
            $completenessCollectionNormalizer,
            $variantProductRatioQuery,
            $imageAsLabel,
            $catalogContext,
            $attributeOptionRepository,
            $completenessCalculator,
            $simpleSelectOptionNormalizer,
            $metricNormalizer,
            $booleanNormalizer
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
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        CompletenessCalculator $completenessCalculator,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        AxisValueLabelsNormalizer $simpleSelectOptionNormalizer,
        AxisValueLabelsNormalizer $metricNormalizer,
        ProductInterface $variantProduct,
        AttributeInterface $colorAttribute,
        AttributeInterface $sizeAttribute,
        AttributeInterface $weightAttribute,
        AttributeOptionInterface $whiteOption,
        AttributeOptionInterface $sOption,
        MetricInterface $weightData
    ) {
        $context = [
            'locale' => 'en_US'
        ];
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);

        $variantProduct->isVariant()->willReturn(true);
        $variantProduct->getLabel('fr_FR')->willReturn('Tshirt Blanc S');
        $variantProduct->getLabel('en_US')->willReturn('Tshirt White S');
        $variantProduct->getIdentifier()->willReturn('tshirt_white_s');
        $variantProduct->getImage()->willReturn(null);
        $variantProduct->getId()->willReturn(42);

        $attributesProvider->getAxes($variantProduct)->willReturn([
            $colorAttribute,
            $sizeAttribute,
            $weightAttribute,
        ]);

        $colorAttribute->getCode()->willReturn('color');
        $colorAttribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $sizeAttribute->getCode()->willReturn('size');
        $sizeAttribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $weightAttribute->getCode()->willReturn('weight');
        $weightAttribute->getType()->willReturn(AttributeTypes::METRIC);

        $whiteValue = ScalarValue::value('color', 'white');
        $variantProduct->getValue('color')->willReturn($whiteValue);
        $sValue = ScalarValue::value('size', 's');
        $variantProduct->getValue('size')->willReturn($sValue);
        $weightData->getData()->willReturn(10.0);
        $weightData->getUnit()->willReturn('KILOGRAM');
        $weightValue = MetricValue::value('weight', $weightData->getWrappedObject());
        $variantProduct->getValue('weight')->willReturn($weightValue);

        $whiteOption->getSortOrder()->willReturn(2);
        $whiteOption->getCode()->willReturn('white');
        $attributeOptionRepository->findOneByIdentifier('color.white')->willReturn($whiteOption);
        $sOption->getSortOrder()->willReturn(1);
        $sOption->getCode()->willReturn('s');
        $attributeOptionRepository->findOneByIdentifier('size.s')->willReturn($sOption);

        $completenessCollection = new ProductCompletenessWithMissingAttributeCodesCollection(42, [
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 0, []),
            new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 0, [])
        ]);

        $completenessCalculator->fromProductIdentifier('tshirt_white_s')->willReturn($completenessCollection);
        $completenessCollectionNormalizer->normalize($completenessCollection)->willReturn(['NORMALIZED_COMPLETENESS']);

        $simpleSelectOptionNormalizer->normalize($whiteValue, 'fr_FR')->willReturn('Blanc');
        $simpleSelectOptionNormalizer->normalize($whiteValue, 'en_US')->willReturn('White');
        $simpleSelectOptionNormalizer->normalize($sValue, 'fr_FR')->willReturn('S');
        $simpleSelectOptionNormalizer->normalize($sValue, 'en_US')->willReturn('S');
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
            'order'              => [2, 'white', 1, 's', 'KILOGRAM', 10.0],
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

    function it_normalizes_an_entity_with_family_variant_with_a_boolean_attribute_as_axis(
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantProductRatioInterface $variantProductRatioQuery,
        AxisValueLabelsNormalizer $simpleSelectOptionNormalizer,
        AxisValueLabelsNormalizer $metricNormalizer,
        AxisValueLabelsNormalizer $booleanNormalizer,
        ProductModelInterface $productModel,
        AttributeInterface $booleanAttribute,
        CompleteVariantProducts $completeVariantProducts
    ) {
        $context = [
            'locale' => 'en_US'
        ];

        $boolValue = ScalarValue::value('a_yes_no', false);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $productModel->getLabel('fr_FR', null)->willReturn('Tshirt Non');
        $productModel->getLabel('en_US', null)->willReturn('Tshirt No');
        $productModel->getId()->willReturn(5);
        $productModel->getCode()->willReturn('tshirt_no');
        $productModel->getValue('a_yes_no')->willReturn($boolValue);

        $booleanAttribute->getCode()->willReturn('a_yes_no');
        $booleanAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);

        $attributesProvider->getAxes($productModel)->willReturn([$booleanAttribute]);

        $simpleSelectOptionNormalizer->supports(AttributeTypes::BOOLEAN)->willReturn(false);
        $metricNormalizer->supports(AttributeTypes::BOOLEAN)->willReturn(false);
        $booleanNormalizer->supports(AttributeTypes::BOOLEAN)->willReturn(true);
        $booleanNormalizer->normalize($boolValue, 'en_US')->willReturn('0');
        $booleanNormalizer->normalize($boolValue, 'fr_FR')->willReturn('0');

        $variantProductRatioQuery->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn(['NORMALIZED COMPLETENESS']);
        $this->normalize($productModel, 'internal_api', $context)->shouldReturn(
            [
                'id' => 5,
                'identifier' => 'tshirt_no',
                'axes_values_labels' => [
                    'fr_FR' => '0',
                    'en_US' => '0',
                ],
                'labels' => [
                    'fr_FR' => 'Tshirt Non',
                    'en_US' => 'Tshirt No',
                ],
                'order' => ['0'],
                'image' => null,
                'model_type' => 'product_model',
                'completeness' => ['NORMALIZED COMPLETENESS'],
            ]
        );
    }
}
