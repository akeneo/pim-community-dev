<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
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
        FormProviderInterface $formProvider
    ) {
        $this->beConstructedWith(
            $productNormalizer,
            $versionNormalizer,
            $versionManager,
            $localeRepository,
            $structureVersionProvider,
            $formProvider
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
        ProductInterface $mug,
        AssociationInterface $upsell,
        AssociationTypeInterface $groupType,
        GroupInterface $group,
        ArrayCollection $groups
    ) {
        $productNormalizer->normalize($mug, 'json', [])->willReturn(
            ['normalized_property' => 'a nice normalized property']
        );

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
