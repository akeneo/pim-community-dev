<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers;
use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        StructureVersionProviderInterface $structureVersionProvider,
        VersionManager $versionManager,
        NormalizerInterface $versionNormalizer,
        GetGroupProductIdentifiers $getGroupProductIdentifiers
    ) {
        $this->beConstructedWith(
            $normalizer,
            $structureVersionProvider,
            $versionManager,
            $versionNormalizer,
            $getGroupProductIdentifiers
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
        GetGroupProductIdentifiers $getGroupProductIdentifiers,
        GroupInterface $tshirt,
        Version $oldestLog,
        Version $newestLog
    ) {
        $options = [
            'decimal_separator' => ',',
            'date_format'       => 'dd/MM/yyyy',
        ];

        $groupNormalized = [
            'code' => 'my_group',
            'type' => 'related',
        ];

        $normalizer->normalize($tshirt, 'standard', $options)->willReturn($groupNormalized);

        $structureVersionProvider->getStructureVersion()->willReturn(1);
        $versionManager->getOldestLogEntry($tshirt)->willReturn($oldestLog);
        $versionManager->getNewestLogEntry($tshirt)->willReturn($newestLog);
        $versionNormalizer->normalize($oldestLog, 'internal_api')->willReturn('normalized_oldest_log');
        $versionNormalizer->normalize($newestLog, 'internal_api')->willReturn('normalized_newest_log');

        $tshirt->getId()->willReturn(12);

        $getGroupProductIdentifiers->byGroupId(12)->willReturn(['product_42', 'product_123']);

        $this->normalize($tshirt, 'internal_api', $options)->shouldReturn(
            [
                'code'     => 'my_group',
                'type'     => 'related',
                'products' => ['product_42', 'product_123'],
                'meta'     => [
                    'id'                => 12,
                    'form'              => 'pim-group-edit-form',
                    'structure_version' => 1,
                    'model_type'        => 'group',
                    'created'           => 'normalized_oldest_log',
                    'updated'           => 'normalized_newest_log',
                ]
            ]
        );
    }
}
