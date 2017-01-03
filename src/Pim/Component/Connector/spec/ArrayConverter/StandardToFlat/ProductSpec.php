<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

class ProductSpec extends ObjectBehavior
{
    function let(
        ProductValueConverter $valueConverter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $identifierAttribute
    ) {
        $attributeRepository->getIdentifier()->willReturn($identifierAttribute);
        $identifierAttribute->getCode()->willReturn('sku');

        $this->beConstructedWith($valueConverter, $attributeRepository);
    }

    function it_converts_from_standard_to_flat_format($valueConverter)
    {
        $valueConverter->convertAttribute('sku',
            [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '10699783'
                ]
            ]
        )->willReturn(['sku' => '10699783']);

        $valueConverter->convertAttribute('weight',
            [
                [
                    'locale' => 'de_DE',
                    'scope'  => 'print',
                    'data'   => [
                        'unit' => 'KILOGRAM',
                        'data' => '100'
                    ]
                ]
            ]
        )->willReturn([
            'weight-de_DE-print' => '100',
            'weight-de_DE-print-unit' => 'KILOGRAM',
        ]);

        $expected = [
            'categories'              => 'audio_video_sales,loudspeakers,sony',
            'enabled'                 => '1',
            'family'                  => 'loudspeakers',
            'groups'                  => 'sound,audio,mp3,speakers',
            'UPSELL-groups'           => '',
            'UPSELL-products'         => '',
            'X_SELL-groups'           => 'akeneo_tshirt,oro_tshirt',
            'X_SELL-products'         => 'AKN_TS,ORO_TSH',
            'sku'                     => '10699783',
            'weight-de_DE-print'      => '100',
            'weight-de_DE-print-unit' => 'KILOGRAM',
        ];

        $item = [
            'categories'        => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled'           => true,
            'family'            => 'loudspeakers',
            'groups'            => ['sound', 'audio', 'mp3'],
            'variant_group'     => 'speakers',
            'associations'      => [
                'UPSELL' => [
                    'groups'   => [],
                    'products' => []
                ],
                'X_SELL' => [
                    'groups'   => ['akeneo_tshirt', 'oro_tshirt'],
                    'products' => ['AKN_TS', 'ORO_TSH']
                ]
            ],
            'sku'               => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '10699783'
                ]
            ],
            'weight'            => [
                [
                    'locale' => 'de_DE',
                    'scope'  => 'print',
                    'data'   => [
                        'unit' => 'KILOGRAM',
                        'data' => '100'
                    ]
                ]
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
