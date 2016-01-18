<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
        LocaleManager $localeManager,
        StructureVersionProviderInterface $structureVersionProvider,
        FormProviderInterface $formProvider
    ) {
        $this->beConstructedWith(
            $productNormalizer,
            $versionNormalizer,
            $versionManager,
            $localeManager,
            $structureVersionProvider,
            $formProvider
        );
    }

    function it_supports_products(ProductInterface $mug)
    {
        $this->supportsNormalization($mug, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_products($productNormalizer, $versionNormalizer, $versionManager, $localeManager, $structureVersionProvider, $formProvider, ProductInterface $mug, AssociationInterface $upsell, AssociationTypeInterface $groupType, GroupInterface $group, ArrayCollection $groups)
    {
        $productNormalizer->normalize($mug, 'json', [])->willReturn(
            ['normalized_property' => 'a nice normalized property']
        );

        $mug->getId()->willReturn(12);
        $versionManager->getOldestLogEntry($mug)->willReturn('create_version');
        $versionNormalizer->normalize('create_version', 'internal_api')->willReturn('normalized_create_version');
        $versionManager->getNewestLogEntry($mug)->willReturn('update_version');
        $versionNormalizer->normalize('update_version', 'internal_api')->willReturn('normalized_update_version');

        $localeManager->getActiveCodes()->willReturn(['en_US', 'fr_FR']);
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

        $this->normalize($mug, 'internal_api', [])->shouldReturn([
            'normalized_property' => 'a nice normalized property',
            'meta' => [
                'form'    => 'product-edit-form',
                'id'      => 12,
                'created' => 'normalized_create_version',
                'updated' => 'normalized_update_version',
                'model_type'        => 'product',
                'structure_version' => 12,
                'label'   => [
                    'en_US' => 'A nice Mug!',
                    'fr_FR' => 'Un très beau Mug !'
                ],
                'associations' => [
                    'group' => ['groupIds' => [12]]
                ],
            ]
        ]);
    }
}
