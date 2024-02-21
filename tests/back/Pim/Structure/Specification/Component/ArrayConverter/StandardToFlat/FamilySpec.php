<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class FamilySpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'                => 'pc_monitors',
            'attributes'          => 'sku,name,description,price',
            'attribute_as_label'  => 'name',
            'requirements-mobile' => 'sku,name',
            'requirements-print'  => 'sku,name,description',
            'label-fr_FR'         => 'Moniteurs',
            'label-en_US'         => 'PC Monitors',
        ];

        $item = [
            'code'                   => 'pc_monitors',
            'attributes'             => [
                'sku',
                'name',
                'description',
                'price'
            ],
            'attribute_as_label'     => 'name',
            'attribute_requirements' => [
                'mobile' => [
                    'sku',
                    'name'
                ],
                'print'  => [
                    'sku',
                    'name',
                    'description'
                ],
            ],
            'labels'                 => [
                'fr_FR' => 'Moniteurs',
                'en_US' => 'PC Monitors',
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
