<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionedAttributeNormalizerSpec extends ObjectBehavior
{
    public function let(
        NormalizerInterface $normalizer,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        StructureVersionProviderInterface $structureVersionProvider
    ) {
        $this->beConstructedWith(
            $normalizer,
            $versionManager,
            $versionNormalizer,
            $structureVersionProvider
        );
    }

    function it_normalizes_an_attribute(
        $normalizer,
        $versionManager,
        $versionNormalizer,
        $structureVersionProvider,
        AttributeInterface $price,
        Version $firstVersion,
        Version $lastVersion
    ) {
        $normalizer->normalize($price, 'internal_api', Argument::any())->willReturn([]);

        $versionManager->getOldestLogEntry($price)->willReturn($firstVersion);
        $versionManager->getNewestLogEntry($price)->willReturn($lastVersion);
        $versionNormalizer->normalize($firstVersion, 'internal_api', [])->willReturn('normalizedFirstVersion');
        $versionNormalizer->normalize($lastVersion, 'internal_api', [])->willReturn('normalizedLastVersion');
        $price->getId()->willReturn(12);
        $structureVersionProvider->getStructureVersion()->willReturn(123789);

        $this->normalize($price, 'internal_api', [])->shouldReturn(
            [
                'meta' => [
                    'created'           => 'normalizedFirstVersion',
                    'updated'           => 'normalizedLastVersion',
                    'structure_version' => 123789,
                    'model_type'        => 'attribute',
                ],
            ]
        );
    }
}
