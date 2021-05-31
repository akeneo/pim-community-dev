<?php

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use PhpSpec\ObjectBehavior;

class AssetItemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetItem::class);
    }

    function it_normalizes_a_read_model()
    {
        $this->identifier = 'designer_starck_fingerprint';
        $this->code = 'starck';
        $this->assetFamilyIdentifier = 'designer';
        $this->labels = [
            'fr_FR' => 'Philippe starck',
            'en_US' => 'Philip starck',
        ];
        $this->image = [];
        $this->values = [
            'designer_name_fingerprint_en_US' => [
                'attribute' => 'designer_name_fingerprint',
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'A nice name'
            ]
        ];
        $this->completeness = ['complete' => 0, 'required' => 0];

        $this->normalize()->shouldReturn(
            [
                'identifier'                 => 'designer_starck_fingerprint',
                'asset_family_identifier' => 'designer',
                'code' => 'starck',
                'labels'                     => [
                    'fr_FR' => 'Philippe starck',
                    'en_US' => 'Philip starck',
                ],
                'image' => [],
                'values' => [
                    'designer_name_fingerprint_en_US' => [
                        'attribute' => 'designer_name_fingerprint',
                        'channel' => null,
                        'locale' => 'en_US',
                        'data' => 'A nice name'
                    ]
                ],
                'completeness' => [
                    'complete' => 0,
                    'required' => 0,
                ]
            ]
        );
    }
}
