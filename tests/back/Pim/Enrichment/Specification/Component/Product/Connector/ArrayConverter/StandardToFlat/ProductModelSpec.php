<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

class ProductModelSpec extends ObjectBehavior
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
        $valueConverter->convertAttribute('weight',
            [
                [
                    'locale' => 'de_DE',
                    'scope'  => 'print',
                    'data'   => [
                        'unit'   => 'KILOGRAM',
                        'amount' => '100'
                    ]
                ]
            ]
        )->willReturn([
            'weight-de_DE-print' => '100',
            'weight-de_DE-print-unit' => 'KILOGRAM',
        ]);

        $expected = [
            'code'                               => 'apollon',
            'categories'                         => 'audio_video_sales,loudspeakers,sony',
            'family_variant'                     => 'soundspeaker_color',
            'parent'                             => 'parent_model_code',
            'weight-de_DE-print'                 => '100',
            'weight-de_DE-print-unit'            => 'KILOGRAM',
            'PACK-products'                      => '',
            'PACK-products-quantity'             => '',
            'PACK-product_models'                => '',
            'PACK-product_models-quantity'       => '',
            'PRODUCTSET-products'                => 'bag,socks',
            'PRODUCTSET-products-quantity'       => '2|8',
            'PRODUCTSET-product_models'          => 'braided-hat,tall_antelope',
            'PRODUCTSET-product_models-quantity' => '12|24',
        ];

        $item = [
            'code'              => 'apollon',
            'categories'        => ['audio_video_sales', 'loudspeakers', 'sony'],
            'family_variant'    => 'soundspeaker_color',
            'parent'            => 'parent_model_code',
            'values'            => [
                'weight'            => [
                    [
                        'locale' => 'de_DE',
                        'scope'  => 'print',
                        'data'   => [
                            'unit' => 'KILOGRAM',
                            'amount' => '100'
                        ]
                    ]
                ],
            ],
            'quantified_associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                ],
                'PRODUCTSET' => [
                    'products' => [
                        [
                            'identifier' => 'bag',
                            'quantity' => 2
                        ],
                        [
                            'identifier' => 'socks',
                            'quantity' => 8
                        ]
                    ],
                    'product_models' => [
                        [
                            'identifier' => 'braided-hat',
                            'quantity' => 12
                        ],
                        [
                            'identifier' => 'tall_antelope',
                            'quantity' => 24
                        ]
                    ]
                ]
            ],
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
