<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer
    ) {
        $this->beConstructedWith(
            $normalizer,
            $structureVersionProvider,
            $versionManager,
            $versionNormalizer
        );
    }

    function it_supports_groups(GroupInterface $tshirt)
    {
        $this->supportsNormalization($tshirt, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_groups(
        $normalizer,
        $structureVersionProvider,
        $versionManager,
        $versionNormalizer,
        GroupInterface $tshirt,
        Version $oldestLog,
        Version $newestLog,
        ArrayCollection $products,
        ProductInterface $product
    ) {
        $normalizer->normalize($tshirt, 'json', [])->willReturn([
            'normalized_property'          => 'the_first_one',
            'an_other_normalized_property' => 'the_second_one',
        ]);

        $structureVersionProvider->getStructureVersion()->willReturn(1);
        $versionManager->getOldestLogEntry($tshirt)->willReturn($oldestLog);
        $versionManager->getNewestLogEntry($tshirt)->willReturn($newestLog);
        $versionNormalizer->normalize($oldestLog, 'internal_api')->willReturn('normalized_oldest_log');
        $versionNormalizer->normalize($newestLog, 'internal_api')->willReturn('normalized_newest_log');
        $products->toArray()->willReturn([$product]);
        $product->getId()->willReturn(42);

        $tshirt->getId()->willReturn(12);
        $tshirt->getProducts()->willReturn($products);

        $this->normalize($tshirt, 'internal_api')->shouldReturn([
            'normalized_property'          => 'the_first_one',
            'an_other_normalized_property' => 'the_second_one',
            'products' => [42],
            'meta'                         => [
                'id'                => 12,
                'structure_version' => 1,
                'model_type'        => 'variant_group',
                'created'           => 'normalized_oldest_log',
                'updated'           => 'normalized_newest_log',
            ]
        ]);
    }
}
