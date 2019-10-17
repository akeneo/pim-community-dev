<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Category\Query\AscendantCategoriesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VariantNavigationNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductModelValues;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        UserContext $userContext,
        MissingAssociationAdder $missingAssociationAdder,
        NormalizerInterface $parentAssociationsNormalizer,
        CatalogContext $catalogContext,
        MissingRequiredAttributesCalculator $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer
    ) {
        $this->beConstructedWith(
            $normalizer,
            $versionNormalizer,
            $versionManager,
            $imageNormalizer,
            $localizedConverter,
            $productValueConverter,
            $formProvider,
            $localeRepository,
            $fillMissingProductModelValues,
            $attributesProvider,
            $navigationNormalizer,
            $variantProductRatioQuery,
            $imageAsLabel,
            $ascendantCategoriesQuery,
            $userContext,
            $missingAssociationAdder,
            $parentAssociationsNormalizer,
            $catalogContext,
            $missingRequiredAttributesCalculator,
            $missingRequiredAttributesNormalizer
        );
    }

    function it_supports_product_models(ProductModelInterface $productModel)
    {
        $this->supportsNormalization($productModel, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_product_models(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        UserContext $userContext,
        MissingRequiredAttributesCalculator $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        AttributeInterface $pictureAttribute,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        ValueInterface $picture,
        CompleteVariantProducts $completeVariantProducts,
        AssociationInterface $association,
        AssociationTypeInterface $associationType,
        GroupInterface $group,
        ArrayCollection $groups
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
            'locale'            => 'en_US',
            'channel'           => 'mobile',
            'timezone'          => 'Pacific/Kiritimati',
        ];

        $productModelNormalized = [
            'code'           => 'tshirt_blue',
            'family_variant' => 'tshirts_color',
            'family'         => 'tshirts',
            'categories'     => ['summer'],
            'values'         => [
                'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
                'number'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'metric'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'prices'              => [['data' => 12.5, 'locale' => null, 'scope' => null]],
                'date'                => [['data' => '2015-01-31', 'locale' => null, 'scope' => null]],
                'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
            ]
        ];

        $familyVariantNormalized = [
            'code'                   => 'tshirts_color',
            'labels'                 => ['en_US' => 'Tshirts Color', 'fr_FR' => 'Tshirt Couleur'],
            'family'                 => 'tshirts',
            'variant_attribute_sets' => []
        ];

        $fileNormalized = [
            'filePath' => 'a/b/c/my_picture.jpg',
            'originalFilename' => 'my_picture.jpg'
        ];

        $valuesLocalized = [
            'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
            'number'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'metric'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'prices'              => [['data' => '12,5', 'locale' => null, 'scope' => null]],
            'date'                => [['data' => '31/01/2015', 'locale' => null, 'scope' => null]],
            'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
        ];

        $userContext->getUserTimezone()->willReturn('Pacific/Kiritimati');
        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
        $fillMissingProductModelValues->fromStandardFormat($productModelNormalized)->willReturn($productModelNormalized);
        $localizedConverter->convertToLocalizedFormats($productModelNormalized['values'], $options)->willReturn($valuesLocalized);

        $valuesConverted = $valuesLocalized;
        $valuesConverted['picture'] = [
            [
                'data' => $fileNormalized,
                'locale' => null,
                'scope' => null
            ]
        ];

        $attributesProvider->getAttributes($productModel)->willReturn([$pictureAttribute]);
        $attributesProvider->getAxes($productModel)->willReturn([$pictureAttribute]);
        $pictureAttribute->getCode()->willReturn('picture');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $productModel->getLabel('en_US', 'mobile')->willReturn('Tshirt blue');
        $productModel->getLabel('fr_FR', 'mobile')->willReturn('Tshirt bleu');

        $imageAsLabel->value($productModel)->willReturn($picture);
        $imageNormalizer->normalize($picture, Argument::any())->willReturn($fileNormalized);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $productModel->getId()->willReturn(12);
        $productModel->getCode()->willReturn('tshirt_blue');
        $versionManager->getOldestLogEntry($productModel)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api', ['timezone' => 'Pacific/Kiritimati'])
            ->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api', ['timezone' => 'Pacific/Kiritimati'])
            ->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $navigationNormalizer->normalize($productModel, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $variantProductRatioQuery->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn([
            'completenesses' => [],
            'total' => 10,
        ]);

        $ascendantCategoriesQuery->getCategoryIds($productModel)->willReturn([42]);

        $productCompletenessWithMissingAttributeCodesCollection = new ProductCompletenessWithMissingAttributeCodesCollection(
            12, []
        );
        $missingRequiredAttributesCalculator->fromEntityWithFamily($productModel)->willReturn(
            $productCompletenessWithMissingAttributeCodesCollection
        );
        $missingRequiredAttributesNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)
            ->willReturn(['kind of completenesses data normalized here']);

        $productModel->getVariationLevel()->willReturn(0);

        $productModel->getAssociations()->willReturn([$association]);
        $association->getAssociationType()->willReturn($associationType);
        $associationType->getCode()->willReturn('group');
        $association->getGroups()->willReturn($groups);
        $groups->toArray()->willReturn([$group]);
        $group->getId()->willReturn(12);
        $association->getGroups()->willReturn($groups);

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'code'           => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
                'parent_associations' => null,
                'meta'           => [
                    'variant_product_completenesses' => [
                        'completenesses' => [],
                        'total' => 10,
                    ],
                    'family_variant' => $familyVariantNormalized,
                    'form'           => 'pim-product-model-edit-form',
                    'id'             => 12,
                    'created'        => 'normalized_create_version',
                    'updated'        => 'normalized_update_version',
                    'model_type'     => 'product_model',
                    'attributes_for_this_level' => ['picture'],
                    'attributes_axes' => ['picture'],
                    'image'          => $fileNormalized,
                    'variant_navigation' => ['NAVIGATION NORMALIZED'],
                    'ascendant_category_ids' => [42],
                    'required_missing_attributes' => ['kind of completenesses data normalized here'],
                    'level'          => 0,
                    'label'          => [
                        'en_US' => 'Tshirt blue',
                        'fr_FR' => 'Tshirt bleu',
                    ],
                    'associations' => [
                        'group' => [
                            'groupIds' => [12]
                        ]
                    ],
                ],
            ]
        );
    }

    function it_normalizes_product_models_without_image(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        UserContext $userContext,
        MissingRequiredAttributesCalculator $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        AttributeInterface $pictureAttribute,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        CompleteVariantProducts $completeVariantProducts
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
            'locale'            => 'en_US',
            'channel'           => 'mobile',
        ];

        $productModelNormalized = [
            'code'           => 'tshirt_blue',
            'family_variant' => 'tshirts_color',
            'family'         => 'tshirts',
            'categories'     => ['summer'],
            'values'         => [
                'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
                'number'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'metric'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'prices'              => [['data' => 12.5, 'locale' => null, 'scope' => null]],
                'date'                => [['data' => '2015-01-31', 'locale' => null, 'scope' => null]],
                'picture'             => [['data' => null, 'locale' => null, 'scope' => null]]
            ]
        ];

        $familyVariantNormalized = [
            'code'                   => 'tshirts_color',
            'labels'                 => ['en_US' => 'Tshirts Color', 'fr_FR' => 'Tshirt Couleur'],
            'family'                 => 'tshirts',
            'variant_attribute_sets' => []
        ];

        $valuesLocalized = [
            'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
            'number'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'metric'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'prices'              => [['data' => '12,5', 'locale' => null, 'scope' => null]],
            'date'                => [['data' => '31/01/2015', 'locale' => null, 'scope' => null]],
            'picture'             => [['data' => null, 'locale' => null, 'scope' => null]]
        ];

        $userContext->getUserTimezone()->willReturn('UTC');
        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
        $fillMissingProductModelValues->fromStandardFormat($productModelNormalized)->willReturn($productModelNormalized);
        $localizedConverter->convertToLocalizedFormats($productModelNormalized['values'], $options)->willReturn($valuesLocalized);

        $valuesConverted = $valuesLocalized;

        $attributesProvider->getAttributes($productModel)->willReturn([$pictureAttribute]);
        $attributesProvider->getAxes($productModel)->willReturn([$pictureAttribute]);
        $pictureAttribute->getCode()->willReturn('picture');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $productModel->getLabel('en_US', 'mobile')->willReturn('Tshirt blue');
        $productModel->getLabel('fr_FR', 'mobile')->willReturn('Tshirt bleu');

        $imageAsLabel->value($productModel)->willReturn(null);
        $imageNormalizer->normalize(null, Argument::any())->willReturn(null);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $productModel->getId()->willReturn(12);
        $productModel->getCode()->willReturn('tshirt_blue');
        $versionManager->getOldestLogEntry($productModel)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api', ['timezone' => 'UTC'])
            ->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api', ['timezone' => 'UTC'])
            ->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $navigationNormalizer->normalize($productModel, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $variantProductRatioQuery->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn([
            'completenesses' => [],
            'total' => 10,
        ]);

        $ascendantCategoriesQuery->getCategoryIds($productModel)->willReturn([42]);

        $productCompletenessWithMissingAttributeCodesCollection = new ProductCompletenessWithMissingAttributeCodesCollection(
            12, []
        );
        $missingRequiredAttributesCalculator->fromEntityWithFamily($productModel)->willReturn(
            $productCompletenessWithMissingAttributeCodesCollection
        );
        $missingRequiredAttributesNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)
            ->willReturn(['kind of completenesses data normalized here']);

        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getAssociations()->willReturn([]);

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'code'           => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
                'parent_associations' => null,
                'meta'           => [
                    'variant_product_completenesses' => [
                        'completenesses' => [],
                        'total' => 10,
                    ],
                    'family_variant' => $familyVariantNormalized,
                    'form'           => 'pim-product-model-edit-form',
                    'id'             => 12,
                    'created'        => 'normalized_create_version',
                    'updated'        => 'normalized_update_version',
                    'model_type'     => 'product_model',
                    'attributes_for_this_level' => ['picture'],
                    'attributes_axes' => ['picture'],
                    'image'          => null,
                    'variant_navigation' => ['NAVIGATION NORMALIZED'],
                    'ascendant_category_ids' => [42],
                    'required_missing_attributes' => ['kind of completenesses data normalized here'],
                    'level'          => 0,
                    'label'          => [
                        'en_US' => 'Tshirt blue',
                        'fr_FR' => 'Tshirt bleu',
                    ],
                    'associations' => [],
                ]
            ]
        );
    }

    function it_normalizes_product_models_without_multiple_levels(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        FillMissingValuesInterface $fillMissingProductModelValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        UserContext $userContext,
        MissingRequiredAttributesCalculator $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        AttributeInterface $pictureAttribute,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        ValueInterface $picture,
        CompleteVariantProducts $completeVariantProducts
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
            'locale'            => 'en_US',
            'channel'           => 'mobile',
        ];

        $productModelNormalized = [
            'code'           => 'tshirt_blue',
            'family_variant' => 'tshirts_color',
            'family'         => 'tshirts',
            'categories'     => ['summer'],
            'values'         => [
                'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
                'number'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'metric'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'prices'              => [['data' => 12.5, 'locale' => null, 'scope' => null]],
                'date'                => [['data' => '2015-01-31', 'locale' => null, 'scope' => null]],
                'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
            ]
        ];

        $familyVariantNormalized = [
            'code'                   => 'tshirts_color',
            'labels'                 => ['en_US' => 'Tshirts Color', 'fr_FR' => 'Tshirt Couleur'],
            'family'                 => 'tshirts',
            'variant_attribute_sets' => []
        ];

        $fileNormalized = [
            'filePath' => 'a/b/c/my_picture.jpg',
            'originalFilename' => 'my_picture.jpg'
        ];

        $valuesLocalized = [
            'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
            'number'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'metric'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'prices'              => [['data' => '12,5', 'locale' => null, 'scope' => null]],
            'date'                => [['data' => '31/01/2015', 'locale' => null, 'scope' => null]],
            'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
        ];

        $userContext->getUserTimezone()->willReturn('UTC');
        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
        $fillMissingProductModelValues->fromStandardFormat($productModelNormalized)->willReturn($productModelNormalized);
        $localizedConverter->convertToLocalizedFormats($productModelNormalized['values'], $options)->willReturn($valuesLocalized);

        $valuesConverted = $valuesLocalized;
        $valuesConverted['picture'] = [
            [
                'data' => $fileNormalized,
                'locale' => null,
                'scope' => null
            ]
        ];

        $attributesProvider->getAttributes($productModel)->willReturn([$pictureAttribute]);
        $attributesProvider->getAxes($productModel)->willReturn([$pictureAttribute]);
        $pictureAttribute->getCode()->willReturn('picture');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $productModel->getLabel('en_US', 'mobile')->willReturn('Tshirt blue');
        $productModel->getLabel('fr_FR', 'mobile')->willReturn('Tshirt bleu');

        $imageAsLabel->value($productModel)->willReturn($picture);
        $imageNormalizer->normalize($picture, Argument::any())->willReturn($fileNormalized);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $productModel->getId()->willReturn(12);
        $productModel->getCode()->willReturn('tshirt_blue');
        $versionManager->getOldestLogEntry($productModel)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api', ['timezone' => 'UTC'])
            ->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api', ['timezone' => 'UTC'])
            ->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $navigationNormalizer->normalize($productModel, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $variantProductRatioQuery->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn([
            'completenesses' => [],
            'total' => 10,
        ]);
        $ascendantCategoriesQuery->getCategoryIds($productModel)->willReturn([42]);

        $productCompletenessWithMissingAttributeCodesCollection = new ProductCompletenessWithMissingAttributeCodesCollection(
            12, []
        );
        $missingRequiredAttributesCalculator->fromEntityWithFamily($productModel)->willReturn(
            $productCompletenessWithMissingAttributeCodesCollection
        );
        $missingRequiredAttributesNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)
                               ->willReturn(['kind of completenesses data normalized here']);

        $productModel->getVariationLevel()->willReturn(0);
        $productModel->getAssociations()->willReturn([]);

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'code'           => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
                'parent_associations' => null,
                'meta'           => [
                    'variant_product_completenesses' => [
                        'completenesses' => [],
                        'total' => 10,
                    ],
                    'family_variant' => $familyVariantNormalized,
                    'form'           => 'pim-product-model-edit-form',
                    'id'             => 12,
                    'created'        => 'normalized_create_version',
                    'updated'        => 'normalized_update_version',
                    'model_type'     => 'product_model',
                    'attributes_for_this_level' => ['picture'],
                    'attributes_axes' => ['picture'],
                    'image'          => $fileNormalized,
                    'variant_navigation' => ['NAVIGATION NORMALIZED'],
                    'ascendant_category_ids' => [42],
                    'required_missing_attributes' => ['kind of completenesses data normalized here'],
                    'level'          => 0,
                    'label'          => [
                        'en_US' => 'Tshirt blue',
                        'fr_FR' => 'Tshirt bleu',
                    ],
                    'associations' => [],
                ]
            ]
        );
    }
}
