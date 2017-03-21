<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;

/**
 * @require \MongoId
 */
class FamilyTransformerSpec extends ObjectBehavior
{
    function it_transforms_product_family_result()
    {
        $locale = 'fr_FR';
        $result = [
            'normalizedData' => [
                'family' => [
                    'code' => 'mongo',
                    'labels' => [
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

    function it_transforms_product_family_label_if_empty()
    {
        $result = [
            'normalizedData' => [
                'family' => [
                    'code' => 'expected-code',
                    'labels' => ['fr_FR' => ''],
                ]
            ]
        ];

        $expected = $result + ['familyLabel'  => '[expected-code]'];

        $this->transform($result, 'fr_FR')->shouldReturn($expected);
    }
}
