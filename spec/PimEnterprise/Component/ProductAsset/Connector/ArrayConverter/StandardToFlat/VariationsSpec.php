<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class VariationsSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $item = [
            'asset'          => 'cat',
            'code'           => 'a/b/c/g/reftguhjik_kitten.jpg',
            'locale'         => 'en_US',
            'channel'        => 'ecommerce',
            'reference_file' => 'a/b/c/g/reftguhjik_cat.jpg',
        ];

        $expected = $item;
        $expected['variation_file'] = $item['code'];
        unset($expected['code']);

        $this->convert($item)->shouldReturn($expected);
    }
}
