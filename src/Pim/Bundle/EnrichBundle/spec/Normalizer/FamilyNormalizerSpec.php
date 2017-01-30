<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer
    ) {
        $this->beConstructedWith(
            $normalizer,
            $versionManager,
            $versionNormalizer
        );
    }

    function it_is_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_internal_api_and_family(FamilyInterface $family)
    {
        $this->supportsNormalization($family, 'internal_api')
            ->shouldReturn(true);
        $this->supportsNormalization(new \StdClass(), 'internal_api')
            ->shouldReturn(false);
        $this->supportsNormalization([], 'internal_api')
            ->shouldReturn(false);
        $this->supportsNormalization($family, 'standard')
            ->shouldReturn(false);
    }

    function it_normalizes_family(
        FamilyInterface $family,
        NormalizerInterface $normalizer,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer
    ) {
        $family->getId()->willReturn(1);
        $normalizer->normalize($family, 'standard', [])->willReturn(
            [
                'code' => 'tshirts',
                'attributes' => [
                    'sku',
                    'name',
                    'description',
                ],
                'attribute_as_label' => 'name',
                'attribute_requirements' => [
                    'general' => [
                        'sku'
                    ],
                    'web' => [
                        'name',
                        'description',
                    ],
                ],
                'labels' => [
                    'en_US' => 'T-shirts'
                ]
            ]
        );
        $versionManager->getOldestLogEntry($family)->shouldBeCalled();
        $versionManager->getNewestLogEntry($family)->shouldBeCalled();

        $this->normalize(
            $family,
            null,
            []
        )->shouldReturn(
            [
                'code' => 'tshirts',
                'attributes' => [
                    'sku',
                    'name',
                    'description',
                ],
                'attribute_as_label' => 'name',
                'attribute_requirements' => [
                    'general' => [
                        'sku'
                    ],
                    'web' => [
                        'name',
                        'description',
                    ],
                ],
                'labels' => [
                    'en_US' => 'T-shirts'
                ],
                'meta' => [
                    'id' => 1,
                    'form' => 'pim-family-edit-form',
                    'created' => null,
                    'updated' => null,
                ]
            ]
        );
    }
}
