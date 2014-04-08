<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyTransformerSpec extends ObjectBehavior
{
    /**
     * @require \MongoId
     */
    function it_transforms_product_family_result(\MongoId $id)
    {
        $locale = 'fr_FR';
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
            'name' => [
                'text' => 'My name',
                'attribute' => [
                    'backendType' => 'text',
                ]
            ]
        ];

        $expected = $result + [
            'familyLabel'  => 'Famille MongoDB',
            'productLabel' => 'My name'
        ];

        $this->transform($result, $locale)->shouldReturn($expected);
    }
}
