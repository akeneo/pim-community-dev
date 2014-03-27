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
        $result = [
            'normalizedData' => [
                'family' => [
                    'code' => 'mongo',
                    'label' => [
                        'en_US' => 'MongoDB Family',
                        'fr_FR' => 'Famille MongoDB'
                    ],
                    'attributeAsLabel' => 'name'
                ]
            ],
            'groups' => [1, 2, 3]
        ];

        $expected = $result + [
            'in_group'  => true
        ];

        $this->transform($result, $locale, $groupId)->shouldReturn($expected);
    }
}
