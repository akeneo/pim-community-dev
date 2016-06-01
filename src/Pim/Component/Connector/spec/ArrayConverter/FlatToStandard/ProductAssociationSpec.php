<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

class ProductAssociationSpec extends ObjectBehavior
{
    function let(
        Product $productConverter,
        AttributeColumnsResolver $attrColumnsResolver
    ) {
        $this->beConstructedWith(
            $productConverter,
            $attrColumnsResolver
        );
    }

    function it_converts(
        $productConverter,
        $attrColumnsResolver
    ) {
        $item = [
            'sku'                    => '1069978',
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'XSELL-groups'           => 'akeneo_tshirt, oro_tshirt',
            'XSELL-products'         => 'AKN_TS, ORO_TSH'
        ];

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $resultItem = [
            'sku' => '1069978',
            'enabled' => true,
            'categories' => ['audio_video_sales', 'loudspeakers', 'sony'],
            'name' => [['data' => 'Sony SRS-BTV25', 'locale' => null, 'scope' => null]],
            'release_date' => [['data' => '2011-08-21', 'locale' => null, 'scope' => 'ecommerce']],
            'associations' => [
                'XSELL' => [
                    'groups' => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $productConverter->convert($item, [])->willReturn($resultItem);

        $filteredItem = [
            'sku' => '1069978',
            'associations' => [
                'XSELL' => [
                    'groups' => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $this
            ->convert($item, [])
            ->shouldReturn($filteredItem);
    }
}
