<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AssetSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'code'        => 'mycode',
            'localized'   => '0',
            'description' => 'My awesome description',
            'categories'  => 'myCat1,myCat2,myCat3',
            'tags'        => 'dog,flowers,cities,animal,sunset',
            'end_of_use'  => '2018-02-01',
        ];

        $item = [
            'code'        => 'mycode',
            'localizable' => false,
            'description' => 'My awesome description',
            'categories'  => [
                'myCat1',
                'myCat2',
                'myCat3',
            ],
            'tags'        => [
                'dog',
                'flowers',
                'cities',
                'animal',
                'sunset',
            ],
            'end_of_use'  => '2018-02-01T00:00:00+02:00',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
