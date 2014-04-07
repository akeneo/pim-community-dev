<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GroupsTransformerSpec extends ObjectBehavior
{
    function it_transforms_product_groups_result(\MongoId $id)
    {
        $locale = 'fr_FR';
        $groupId = 2;
        $normalizedData = [
            'normalizedData' => [
                'groups' => [
                    [
                        'code'  => 'akeneo_related',
                        'label' => [
                            'en_US' => 'Akeneo related',
                            'fr_FR' => 'Akeneo liés'
                        ]
                    ],
                    [
                        'code'  => 'akeneo_tshirt',
                        'label' => [
                            'en_US' => 'Akeneo tshirt',
                            'fr_FR' => 'Tshirt Akeneo'
                        ]
                    ]
                ]
            ],
        ];
        $result = $normalizedData + ['groups' => [1, 2, 3]];

        $expected = $normalizedData + [
            'groups'   => [
                'akeneo_related' => [
                    'code'  => 'akeneo_related',
                    'label' => 'Akeneo liés'
                ],
                'akeneo_tshirt' => [
                    'code'  => 'akeneo_tshirt',
                    'label' => 'Tshirt Akeneo'
                ]
            ],
            'is_checked' => true,
            'in_group' => true
        ];

        $this->transform($result, $locale, $groupId)->shouldReturn($expected);
    }
}
