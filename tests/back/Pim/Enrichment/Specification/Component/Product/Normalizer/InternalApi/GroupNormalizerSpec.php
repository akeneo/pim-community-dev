<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Akeneo\Tool\Component\Versioning\Model\Version;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        FindProductUuidsInGroup $findProductUuids
    ) {
        $this->beConstructedWith(
            $normalizer,
            $findProductUuids
        );
    }

    function it_supports_groups(GroupInterface $tshirt)
    {
        $this->supportsNormalization($tshirt, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_groups(
        NormalizerInterface $normalizer,
        FindProductUuidsInGroup $findProductUuids,
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

        $tshirt->getId()->willReturn(12);

        $findProductUuids->forGroupId(12)->shouldBeCalled()->willReturn(['a9bd2243-12d8-4b44-af6a-9a0d9b30c2c2', '48fd174e-34fd-44ec-8e81-f1b7a4cf65c9']);

        $this->normalize($tshirt, 'internal_api', $options)->shouldReturn(
            [
                'code'     => 'my_group',
                'type'     => 'related',
                'products' => ['a9bd2243-12d8-4b44-af6a-9a0d9b30c2c2', '48fd174e-34fd-44ec-8e81-f1b7a4cf65c9'],
                'meta'     => [
                    'id'                => 12,
                    'form'              => 'pim-group-edit-form',
                    'model_type'        => 'group',
                ]
            ]
        );
    }
}
