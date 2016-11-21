<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $productNormalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        LocaleRepositoryInterface $localeRepository,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider,
        AttributeConverterInterface $localizedConverter
    ) {
        $this->beConstructedWith(
            $productNormalizer,
            $versionNormalizer,
            $versionManager,
            $localeRepository,
            $structureVersionProvider,
            $formProvider,
            $localizedConverter
        );
    }

    function it_supports_products(ProductInterface $mug)
    {
        $this->supportsNormalization($mug, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_products(
        $productNormalizer,
        $versionNormalizer,
        $versionManager,
        $localeRepository,
        $structureVersionProvider,
        $formProvider,
        $localizedConverter,
        ProductInterface $mug,
        AssociationInterface $upsell,
        AssociationTypeInterface $groupType,
        GroupInterface $group,
        ArrayCollection $groups
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
                'normalized_property' => ['data' => 'a nice normalized property', 'locale' => null, 'scope' => null],
                'number' => ['data' => 12.5000, 'locale' => null, 'scope' => null],
                'metric' => ['data' => 12.5000, 'locale' => null, 'scope' => null],
                'prices' => ['data' => 12.5, 'locale' => null, 'scope' => null],
                'date'   => ['data' => '2015-01-31', 'locale' => null, 'scope' => null],
            ]
        ];

        $valuesLocalized = [
            'normalized_property' => ['data' => 'a nice normalized property', 'locale' => null, 'scope' => null],
            'number' => ['data' => '12,5000', 'locale' => null, 'scope' => null],
            'metric' => ['data' => '12,5000', 'locale' => null, 'scope' => null],
            'prices' => ['data' => '12,5', 'locale' => null, 'scope' => null],
            'date'   => ['data' => '31/01/2015', 'locale' => null, 'scope' => null],
        ];

        $productNormalizer->normalize($mug, 'standard', $options)->willReturn($productNormalized);
        $localizedConverter->convertToLocalizedFormats($productNormalized['values'], $options)->willReturn($valuesLocalized);

        $mug->getId()->willReturn(12);
        $versionManager->getOldestLogEntry($mug)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($mug)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR']);
        $mug->getLabel('en_US')->willReturn('A nice Mug!');
        $mug->getLabel('fr_FR')->willReturn('Un très beau Mug !');

        $mug->getAssociations()->willReturn([$upsell]);
        $upsell->getAssociationType()->willReturn($groupType);
        $groupType->getCode()->willReturn('group');
        $upsell->getGroups()->willReturn($groups);
        $groups->toArray()->willReturn([$group]);
        $group->getId()->willReturn(12);

        $structureVersionProvider->getStructureVersion()->willReturn(12);
        $formProvider->getForm($mug)->willReturn('product-edit-form');

        $this->normalize($mug, 'internal_api', $options)->shouldReturn(
            [
                'enabled'    => true,
                'categories' => ['kitchen'],
                'family'     => '',
                'values'     => $valuesLocalized,
                'meta'       => [
                    'form'              => 'product-edit-form',
                    'id'                => 12,
                    'created'           => 'normalized_create_version',
                    'updated'           => 'normalized_update_version',
                    'model_type'        => 'product',
                    'structure_version' => 12,
                    'label'             => [
                        'en_US' => 'A nice Mug!',
                        'fr_FR' => 'Un très beau Mug !'
                    ],
                    'associations'      => [
                        'group' => ['groupIds' => [12]]
                    ],
                ]
            ]
        );
    }
}
