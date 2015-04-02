<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\DataGrid\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;

class ReferenceDataTransformerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ReferenceDataBundle\DataGrid\Datasource\ResultRecord\MongoDbOdm\Product\ReferenceDataTransformer');
    }

    function it_transforms_simple_reference_data()
    {
        $attribute = [
            'code'              => 'my-code',
            'attributeType'     => 'pim_reference_data_simpleselect',
            'localizable'       => null,
            'scopable'          => null,
            'backendType'       => 'reference_data_option',
            'properties'        => ['reference_data_name' => 'sole_color'],
        ];

        $result = [
            'normalizedData' => [
                'my-code' => [
                    'id'   => 1,
                    'code' => 'my-code',
                ]
            ]
        ];

        $this->transform($result, $attribute, false, false)->shouldReturn([
            'sole_color' => [
                'id'   => 1,
                'code' => 'my-code'
            ]
        ]);
    }

    function it_transforms_multi_reference_data()
    {
        $attribute = [
            'code'              => 'my-code',
            'attributeType'     => 'pim_reference_data_multiselect',
            'localizable'       => null,
            'scopable'          => null,
            'backendType'       => 'reference_data_options',
            'properties'        => ['reference_data_name' => 'sole_fabric'],
        ];

        $result = [
            'normalizedData' => [
                'my-code' => [
                    [
                        'id'   => 1,
                        'code' => 'my-first-code',
                    ],
                    [
                        'id'   => 2,
                        'code' => 'my-second-code',
                    ],
                ]
            ],
            'values' => [
                'color' => 1,
                'color' => 2,
            ]
        ];

        $this->transform($result, $attribute, false, false)->shouldReturn([
            'sole_fabric' => [
                [
                    'id'   => 1,
                    'code' => 'my-first-code',
                ],
                [
                    'id'   => 2,
                    'code' => 'my-second-code',
                ],
            ]
        ]);
    }
}
