<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Enrichment\Component\Category\Query\AscendantCategoriesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessWithMissingAttributeCodesCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VariantNavigationNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductValues;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        UserContext $userContext,
        FillMissingValuesInterface $fillMissingProductValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        AscendantCategoriesInterface $ascendantCategories,
        MissingAssociationAdder $missingAssociationAdder,
        NormalizerInterface $parentAssociationsNormalizer,
        CatalogContext $catalogContext,
        CompletenessCalculator $completenessCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer
    ) {
        $this->beConstructedWith(
            $normalizer,
            $versionNormalizer,
            $versionManager,
            $imageNormalizer,
            $localeRepository,
            $structureVersionProvider,
            $formProvider,
            $localizedConverter,
            $productValueConverter,
            $completenessCollectionNormalizer,
            $userContext,
            $fillMissingProductValues,
            $attributesProvider,
            $navigationNormalizer,
            $ascendantCategories,
            $missingAssociationAdder,
            $parentAssociationsNormalizer,
            $catalogContext,
            $completenessCalculator,
            $missingRequiredAttributesNormalizer
        );
    }

    function it_supports_products(ProductInterface $mug)
    {
        $this->supportsNormalization($mug, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_products(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        UserContext $userContext,
        FillMissingValuesInterface $fillMissingProductValues,
        MissingAssociationAdder $missingAssociationAdder,
        CompletenessCalculator $completenessCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        ProductInterface $mug,
        AssociationInterface $upsell,
        AssociationTypeInterface $groupType,
        GroupInterface $group,
        ArrayCollection $groups,
        ValueInterface $image
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
            'locale'            => 'en_US',
            'channel'           => 'mobile',
        ];

        $productNormalized = [
            'enabled'    => true,
            'categories' => ['kitchen'],
            'family'     => '',
            'values' => [
                'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
                'number'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'metric'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'prices'              => [['data' => 12.5, 'locale' => null, 'scope' => null]],
                'date'                => [['data' => '2015-01-31', 'locale' => null, 'scope' => null]],
                'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
            ]
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
        $normalizer->normalize($mug, 'standard', $options)->willReturn($productNormalized);
        $fillMissingProductValues->fromStandardFormat($productNormalized)->willReturn($productNormalized);
        $localizedConverter->convertToLocalizedFormats($productNormalized['values'], $options)->willReturn($valuesLocalized);

        $valuesConverted = $valuesLocalized;
        $valuesConverted['picture'] = [
            [
                'data' => [
                    'filePath' => 'a/b/c/my_picture.jpg', 'originalFilename' => 'my_picture.jpg'
                ],
                'locale' => null,
                'scope' => null
            ]
        ];

        $userContext->getUserLocales()->willReturn([]);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $mug->isVariant()->willReturn(false);
        $mug->getId()->willReturn(12);
        $mug->getIdentifier()->willReturn('mug');
        $versionManager->getOldestLogEntry($mug)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api', ['timezone' => 'Pacific/Kiritimati'])
            ->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($mug)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api', ['timezone' => 'Pacific/Kiritimati'])
            ->willReturn('normalized_update_version');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $mug->getLabel('en_US', 'mobile')->willReturn('A nice Mug!');
        $mug->getLabel('fr_FR', 'mobile')->willReturn('Un très beau Mug !');
        $mug->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $mug->getAssociations()->willReturn([$upsell]);
        $upsell->getAssociationType()->willReturn($groupType);
        $groupType->getCode()->willReturn('group');
        $upsell->getGroups()->willReturn($groups);
        $groups->toArray()->willReturn([$group]);
        $group->getId()->willReturn(12);

        $productCompletenessWithMissingAttributeCodesCollection = new ProductCompletenessWithMissingAttributeCodesCollection(12, []);
        $completenessCalculator->fromProductIdentifier('mug')->willReturn($productCompletenessWithMissingAttributeCodesCollection);
        $completenessCollectionNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)
                                         ->willReturn(['normalized_completenesses']);
        $missingRequiredAttributesNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)
                                            ->willReturn(['normalized_missing_attributes']);


        $structureVersionProvider->getStructureVersion()->willReturn(12);
        $formProvider->getForm($mug)->willReturn('product-edit-form');

        $missingAssociationAdder->addMissingAssociations($mug)->shouldBeCalled();

        $this->normalize($mug, 'internal_api', $options)->shouldReturn(
            [
                'enabled'    => true,
                'categories' => ['kitchen'],
                'family'     => '',
                'values'     => $valuesConverted,
                'parent_associations' => null,
                'meta'       => [
                    'form'              => 'product-edit-form',
                    'id'                => 12,
                    'created'           => 'normalized_create_version',
                    'updated'           => 'normalized_update_version',
                    'model_type'        => 'product',
                    'structure_version' => 12,
                    'completenesses'    => ['normalized_completenesses'],
                    'required_missing_attributes' => ['normalized_missing_attributes'],
                    'image'             => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'label'             => [
                        'en_US' => 'A nice Mug!',
                        'fr_FR' => 'Un très beau Mug !'
                    ],
                    'associations' => [
                        'group' => [
                            'groupIds' => [12]
                        ]
                    ],
                    'ascendant_category_ids'    => [],
                    'variant_navigation'        => [],
                    'attributes_for_this_level' => [],
                    'attributes_axes'           => [],
                    'parent_attributes'         => [],
                    'family_variant'            => null,
                    'level'                     => null,
                ]
            ]
        );
    }

    function it_normalizes_variant_products(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $completenessCollectionNormalizer,
        UserContext $userContext,
        FillMissingValuesInterface $fillMissingProductValues,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        AscendantCategoriesInterface $ascendantCategories,
        MissingAssociationAdder $missingAssociationAdder,
        CompletenessCalculator $completenessCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer,
        ProductInterface $mug,
        AssociationInterface $upsell,
        AssociationTypeInterface $groupType,
        GroupInterface $group,
        ArrayCollection $groups,
        ValueInterface $image,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $color,
        AttributeInterface $size,
        AttributeInterface $description,
        ProductModelInterface $productModel
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
            'locale'            => 'en_US',
            'channel'           => 'mobile',
        ];

        $productNormalized = [
            'enabled'    => true,
            'categories' => ['kitchen'],
            'family'     => '',
            'values' => [
                'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
                'number'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'metric'              => [['data' => 12.5000, 'locale' => null, 'scope' => null]],
                'prices'              => [['data' => 12.5, 'locale' => null, 'scope' => null]],
                'date'                => [['data' => '2015-01-31', 'locale' => null, 'scope' => null]],
                'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
            ]
        ];

        $valuesLocalized = [
            'normalized_property' => [['data' => 'a nice normalized property', 'locale' => null, 'scope' => null]],
            'number'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'metric'              => [['data' => '12,5000', 'locale' => null, 'scope' => null]],
            'prices'              => [['data' => '12,5', 'locale' => null, 'scope' => null]],
            'date'                => [['data' => '31/01/2015', 'locale' => null, 'scope' => null]],
            'picture'             => [['data' => 'a/b/c/my_picture.jpg', 'locale' => null, 'scope' => null]]
        ];

        $mug->isVariant()->willReturn(true);
        $userContext->getUserTimezone()->willReturn('Pacific/Kiritimati');
        $normalizer->normalize($mug, 'standard', $options)->willReturn($productNormalized);

        $fillMissingProductValues->fromStandardFormat($productNormalized)->willReturn($productNormalized);

        $localizedConverter->convertToLocalizedFormats($productNormalized['values'], $options)->willReturn($valuesLocalized);

        $valuesConverted = $valuesLocalized;
        $valuesConverted['picture'] = [
            [
                'data' => [
                    'filePath' => 'a/b/c/my_picture.jpg', 'originalFilename' => 'my_picture.jpg'
                ],
                'locale' => null,
                'scope' => null
            ]
        ];

        $userContext->getUserLocales()->willReturn([]);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $mug->getId()->willReturn(12);
        $mug->getIdentifier()->willReturn('mug');
        $versionManager->getOldestLogEntry($mug)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api', ['timezone' => 'Pacific/Kiritimati'])
            ->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($mug)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api', ['timezone' => 'Pacific/Kiritimati'])
            ->willReturn('normalized_update_version');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $mug->getLabel('en_US', 'mobile')->willReturn('A nice Mug!');
        $mug->getLabel('fr_FR', 'mobile')->willReturn('Un très beau Mug !');
        $mug->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $mug->getAssociations()->willReturn([$upsell]);
        $upsell->getAssociationType()->willReturn($groupType);
        $groupType->getCode()->willReturn('group');
        $upsell->getGroups()->willReturn($groups);
        $groups->toArray()->willReturn([$group]);
        $group->getId()->willReturn(12);

        $productCompletenessWithMissingAttributeCodesCollection = new ProductCompletenessWithMissingAttributeCodesCollection(12, []);
        $completenessCalculator->fromProductIdentifier('mug')->willReturn($productCompletenessWithMissingAttributeCodesCollection);
        $completenessCollectionNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)->willReturn([]);
        $missingRequiredAttributesNormalizer->normalize($productCompletenessWithMissingAttributeCodesCollection)->willReturn([]);

        $structureVersionProvider->getStructureVersion()->willReturn(12);
        $formProvider->getForm($mug)->willReturn('product-edit-form');

        $missingAssociationAdder->addMissingAssociations($mug)->shouldBeCalled();

        $navigationNormalizer->normalize($mug, 'internal_api', $options)
            ->willReturn(['NAVIGATION NORMALIZED']);

        $mug->getFamilyVariant()->willReturn($familyVariant);
        $normalizer->normalize($familyVariant, 'standard')->willReturn([
            'NORMALIZED FAMILY'
        ]);

        $mug->getParent()->willReturn($productModel);
        $productModel->getId()->willReturn(42);
        $attributesProvider->getAttributes($mug)->willReturn([$size]);
        $attributesProvider->getAxes($mug)->willReturn([$size]);
        $attributesProvider->getAxes($productModel)->willReturn([]);
        $attributesProvider->getAttributes($productModel)->willReturn([$color, $description]);

        $mug->getVariationLevel()->willReturn(1);

        $color->getCode()->willReturn('color');
        $size->getCode()->willReturn('size');
        $description->getCode()->willReturn('description');

        $ascendantCategories->getCategoryIds($mug)->willReturn([42]);

        $this->normalize($mug, 'internal_api', $options)->shouldReturn(
            [
                'enabled'    => true,
                'categories' => ['kitchen'],
                'family'     => '',
                'values'     => $valuesConverted,
                'parent_associations' => null,
                'meta'       => [
                    'form'              => 'product-edit-form',
                    'id'                => 12,
                    'created'           => 'normalized_create_version',
                    'updated'           => 'normalized_update_version',
                    'model_type'        => 'product',
                    'structure_version' => 12,
                    'completenesses'    => [],
                    'required_missing_attributes' => [],
                    'image'             => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'label'             => [
                        'en_US' => 'A nice Mug!',
                        'fr_FR' => 'Un très beau Mug !'
                    ],
                    'associations' => [
                        'group' => [
                            'groupIds' => [12]
                        ]
                    ],
                    'ascendant_category_ids'    => [42],
                    'variant_navigation' => ['NAVIGATION NORMALIZED'],
                    'attributes_for_this_level' => ['size'],
                    'attributes_axes'           => ['size'],
                    'parent_attributes'         => ['color', 'description'],
                    'family_variant'            => ['NORMALIZED FAMILY'],
                    'level'                     => 1,
                ]
            ]
        );
    }
}
