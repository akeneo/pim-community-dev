<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer;
use Pim\Bundle\EnrichBundle\Normalizer\VariantNavigationNormalizer;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\ProductModel\ImageAsLabel;
use Pim\Component\Catalog\ProductModel\Query\VariantProductRatioInterface;
use Pim\Component\Catalog\ProductModel\Query\CompleteVariantProducts;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Pim\Component\Enrich\Query\AscendantCategoriesInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        ImageNormalizer $imageNormalizer,
        VersionManager $versionManager,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyValuesFillerInterface $entityValuesFiller,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $findVariantProductCompleteness,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategories,
        NormalizerInterface $incompleteValuesNormalizer
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
            $entityValuesFiller,
            $attributesProvider,
            $navigationNormalizer,
            $findVariantProductCompleteness,
            $imageAsLabel,
            $ascendantCategories,
            $incompleteValuesNormalizer
        );
    }

    function it_supports_product_models(ProductModelInterface $productModel)
    {
        $this->supportsNormalization($productModel, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_product_models(
        $normalizer,
        $versionNormalizer,
        $imageNormalizer,
        $versionManager,
        $localizedConverter,
        $productValueConverter,
        $formProvider,
        $localeRepository,
        $attributesProvider,
        $navigationNormalizer,
        $findVariantProductCompleteness,
        $imageAsLabel,
        $ascendantCategories,
        $incompleteValuesNormalizer,
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

        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
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
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $navigationNormalizer->normalize($productModel, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $findVariantProductCompleteness->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn([
            'completenesses' => [],
            'total' => 10,
        ]);

        $ascendantCategories->getCategoryIds($productModel)->willReturn([42]);

        $incompleteValuesNormalizer->normalize($productModel, Argument::cetera())
            ->willReturn(['kind of completenesses data normalized here']);

        $productModel->getVariationLevel()->willReturn(0);

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'code'           => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
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
                ],
            ]
        );
    }

    function it_normalizes_product_models_without_image(
        $normalizer,
        $versionNormalizer,
        $imageNormalizer,
        $versionManager,
        $localizedConverter,
        $productValueConverter,
        $formProvider,
        $localeRepository,
        $attributesProvider,
        $navigationNormalizer,
        $findVariantProductCompleteness,
        $imageAsLabel,
        $ascendantCategories,
        $incompleteValuesNormalizer,
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

        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
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
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $navigationNormalizer->normalize($productModel, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $findVariantProductCompleteness->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn([
            'completenesses' => [],
            'total' => 10,
        ]);

        $ascendantCategories->getCategoryIds($productModel)->willReturn([42]);

        $incompleteValuesNormalizer->normalize($productModel, Argument::cetera())
            ->willReturn(['kind of completenesses data normalized here']);

        $productModel->getVariationLevel()->willReturn(0);

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'code'           => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
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
                ]
            ]
        );
    }

    function it_normalizes_product_models_without_multiple_levels(
        $normalizer,
        $versionNormalizer,
        $imageNormalizer,
        $versionManager,
        $localizedConverter,
        $productValueConverter,
        $formProvider,
        $localeRepository,
        $attributesProvider,
        $navigationNormalizer,
        $findVariantProductCompleteness,
        $imageAsLabel,
        $ascendantCategories,
        $incompleteValuesNormalizer,
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

        $normalizer->normalize($productModel, 'standard', $options)->willReturn($productModelNormalized);
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
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($productModel)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('tshirts');
        $normalizer->normalize($familyVariant, 'standard')->willReturn($familyVariantNormalized);

        $formProvider->getForm($productModel)->willReturn('pim-product-model-edit-form');

        $navigationNormalizer->normalize($productModel, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $findVariantProductCompleteness->findComplete($productModel)->willReturn($completeVariantProducts);
        $completeVariantProducts->values()->willReturn([
            'completenesses' => [],
            'total' => 10,
        ]);

        $ascendantCategories->getCategoryIds($productModel)->willReturn([42]);

        $incompleteValuesNormalizer->normalize($productModel, Argument::cetera())
            ->willReturn(['kind of completenesses data normalized here']);

        $productModel->getVariationLevel()->willReturn(0);

        $this->normalize($productModel, 'internal_api', $options)->shouldReturn(
            [
                'code'           => 'tshirt_blue',
                'family_variant' => 'tshirts_color',
                'family'         => 'tshirts',
                'categories'     => ['summer'],
                'values'         => $valuesConverted,
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
                ]
            ]
        );
    }
}
