<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Product;
use PhpSpec\ObjectBehavior;

class ProductAssociationSpec extends ObjectBehavior
{
    function let(Product $productConverter)
    {
        $this->beConstructedWith($productConverter);
    }

    function it_converts($productConverter)
    {
        $item = [
            'sku'                    => '1069978',
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'XSELL-groups'           => 'akeneo_tshirt, oro_tshirt',
            'XSELL-products'         => 'AKN_TS, ORO_TSH'
        ];

        $resultItem = [
            'identifier' => '1069978',
            'enabled' => true,
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'values'     => [
                'sku' => ['data' => '1069978', 'locale' => null, 'scope' => null],
                'name' => [['data' => 'Sony SRS-BTV25', 'locale' => null, 'scope' => null]],
                'release_date' => [['data' => '2011-08-21', 'locale' => null, 'scope' => 'ecommerce']],
            ],
            'associations' => [
                'XSELL' => [
                    'groups' => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $productConverter->convert($item, [])->willReturn($resultItem);

        $filteredItem = [
            'identifier'   => '1069978',
            'associations' => [
                'XSELL' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $this
            ->convert($item, [])
            ->shouldReturn($filteredItem);
    }
}
