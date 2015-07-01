<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter;

class ProductAssociationStandardConverterSpec extends ObjectBehavior
{
    function let(
        ProductStandardConverter $productConverter,
        AssociationColumnsResolver $assocColumnsResolver,
        AttributeColumnsResolver $attrColumnsResolver
    ) {
        $this->beConstructedWith(
            $productConverter,
            $assocColumnsResolver,
            $attrColumnsResolver
        );
    }

    function it_converts(
        $productConverter,
        $assocColumnsResolver,
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

        $assocColumnsResolver->resolveAssociationColumns()->willReturn(['XSELL-groups', 'XSELL-products']);
        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $filteredItem = [
            'sku'                    => '1069978',
            'XSELL-groups'           => 'akeneo_tshirt, oro_tshirt',
            'XSELL-products'          => 'AKN_TS, ORO_TSH'
        ];

        $resultItem = [
            'sku' => '1069978',
            'associations' => [
                'XSELL' => [
                    'groups' => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $productConverter->convert($filteredItem, [])->willReturn($resultItem);

        $this
            ->convert($item, [])
            ->shouldReturn($resultItem);
    }
}
