<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\AscendantCategories;
use Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer;
use Pim\Bundle\EnrichBundle\Normalizer\VariantNavigationNormalizer;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Pim\Component\Enrich\Query\AscendantCategoriesInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        ObjectManager $productManager,
        CompletenessManager $completenessManager,
        ChannelRepositoryInterface $channelRepository,
        CollectionFilterInterface $collectionFilter,
        NormalizerInterface $completenessCollectionNormalizer,
        UserContext $userContext,
        CompletenessCalculatorInterface $completenessCalculator,
        FileNormalizer $fileNormalizer,
        ProductBuilderInterface $productBuilder,
        EntityWithFamilyValuesFillerInterface $productValuesFiller,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        AscendantCategoriesInterface $ascendantCategories
    ) {
        $this->beConstructedWith(
            $normalizer,
            $versionNormalizer,
            $versionManager,
            $localeRepository,
            $structureVersionProvider,
            $formProvider,
            $localizedConverter,
            $productValueConverter,
            $productManager,
            $completenessManager,
            $channelRepository,
            $collectionFilter,
            $completenessCollectionNormalizer,
            $userContext,
            $completenessCalculator,
            $fileNormalizer,
            $productBuilder,
            $productValuesFiller,
            $attributesProvider,
            $navigationNormalizer,
            $ascendantCategories
        );
    }

    function it_supports_products(ProductInterface $mug)
    {
        $this->supportsNormalization($mug, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_products(
        $normalizer,
        $versionNormalizer,
        $versionManager,
        $localeRepository,
        $structureVersionProvider,
        $formProvider,
        $localizedConverter,
        $productValueConverter,
        $channelRepository,
        $userContext,
        $collectionFilter,
        $fileNormalizer,
        $productBuilder,
        $productValuesFiller,
        ProductInterface $mug,
        AssociationInterface $upsell,
        AssociationTypeInterface $groupType,
        GroupInterface $group,
        ArrayCollection $groups,
        ValueInterface $image,
        FileInfoInterface $dataImage
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
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

        $normalizer->normalize($mug, 'standard', $options)->willReturn($productNormalized);
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

        $channelRepository->getFullChannels()->willReturn([]);
        $userContext->getUserLocales()->willReturn([]);
        $collectionFilter->filterCollection([], 'pim.internal_api.locale.view')->willReturn([]);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $mug->getId()->willReturn(12);
        $versionManager->getOldestLogEntry($mug)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($mug)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $mug->getLabel('en_US')->willReturn('A nice Mug!');
        $mug->getLabel('fr_FR')->willReturn('Un très beau Mug !');
        $mug->getImage()->willReturn($image);
        $image->getData()->willReturn($dataImage);
        $fileNormalizer->normalize($dataImage, Argument::any(), Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $mug->getAssociations()->willReturn([$upsell]);
        $upsell->getAssociationType()->willReturn($groupType);
        $groupType->getCode()->willReturn('group');
        $upsell->getGroups()->willReturn($groups);
        $groups->toArray()->willReturn([$group]);
        $group->getId()->willReturn(12);

        $mug->getCompletenesses()->willReturn(new ArrayCollection(['']));

        $structureVersionProvider->getStructureVersion()->willReturn(12);
        $formProvider->getForm($mug)->willReturn('product-edit-form');

        $productBuilder->addMissingAssociations($mug)->shouldBeCalled();
        $productValuesFiller->fillMissingValues($mug)->shouldBeCalled();

        $this->normalize($mug, 'internal_api', $options)->shouldReturn(
            [
                'enabled'    => true,
                'categories' => ['kitchen'],
                'family'     => '',
                'values'     => $valuesConverted,
                'meta'       => [
                    'form'              => 'product-edit-form',
                    'id'                => 12,
                    'created'           => 'normalized_create_version',
                    'updated'           => 'normalized_update_version',
                    'model_type'        => 'product',
                    'structure_version' => 12,
                    'completenesses'    => null,
                    'image'             => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'label'             => [
                        'en_US' => 'A nice Mug!',
                        'fr_FR' => 'Un très beau Mug !'
                    ],
                    'associations'      => [
                        'group' => ['groupIds' => [12]]
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
        $normalizer,
        $versionNormalizer,
        $versionManager,
        $localeRepository,
        $structureVersionProvider,
        $formProvider,
        $localizedConverter,
        $productValueConverter,
        $channelRepository,
        $userContext,
        $collectionFilter,
        $fileNormalizer,
        $productBuilder,
        $productValuesFiller,
        $navigationNormalizer,
        $attributesProvider,
        $ascendantCategories,
        VariantProductInterface $mug,
        AssociationInterface $upsell,
        AssociationTypeInterface $groupType,
        GroupInterface $group,
        ArrayCollection $groups,
        ValueInterface $image,
        FileInfoInterface $dataImage,
        FamilyVariantInterface $familyVariant,
        AttributeInterface $color,
        AttributeInterface $size,
        AttributeInterface $description,
        ProductModelInterface $productModel
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
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

        $normalizer->normalize($mug, 'standard', $options)->willReturn($productNormalized);
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

        $channelRepository->getFullChannels()->willReturn([]);
        $userContext->getUserLocales()->willReturn([]);
        $collectionFilter->filterCollection([], 'pim.internal_api.locale.view')->willReturn([]);

        $productValueConverter->convert($valuesLocalized)->willReturn($valuesConverted);

        $mug->getId()->willReturn(12);
        $versionManager->getOldestLogEntry($mug)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($mug)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $mug->getLabel('en_US')->willReturn('A nice Mug!');
        $mug->getLabel('fr_FR')->willReturn('Un très beau Mug !');
        $mug->getImage()->willReturn($image);
        $image->getData()->willReturn($dataImage);
        $fileNormalizer->normalize($dataImage, Argument::any(), Argument::any())->willReturn([
            'filePath'         => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $mug->getAssociations()->willReturn([$upsell]);
        $upsell->getAssociationType()->willReturn($groupType);
        $groupType->getCode()->willReturn('group');
        $upsell->getGroups()->willReturn($groups);
        $groups->toArray()->willReturn([$group]);
        $group->getId()->willReturn(12);

        $mug->getCompletenesses()->willReturn(new ArrayCollection(['']));

        $structureVersionProvider->getStructureVersion()->willReturn(12);
        $formProvider->getForm($mug)->willReturn('product-edit-form');

        $productBuilder->addMissingAssociations($mug)->shouldBeCalled();
        $productValuesFiller->fillMissingValues($mug)->shouldBeCalled();

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
                'meta'       => [
                    'form'              => 'product-edit-form',
                    'id'                => 12,
                    'created'           => 'normalized_create_version',
                    'updated'           => 'normalized_update_version',
                    'model_type'        => 'product',
                    'structure_version' => 12,
                    'completenesses'    => null,
                    'image'             => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'label'             => [
                        'en_US' => 'A nice Mug!',
                        'fr_FR' => 'Un très beau Mug !'
                    ],
                    'associations'      => [
                        'group' => ['groupIds' => [12]]
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
