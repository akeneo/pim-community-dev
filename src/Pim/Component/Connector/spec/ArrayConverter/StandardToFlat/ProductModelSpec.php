<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ProductValueConverter;

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
            'code'                    => 'apollon',
            'family_variant'          => 'soundspeaker_color',
            'parent'                  => 'parent_model_code',
            'weight-de_DE-print'      => '100',
            'weight-de_DE-print-unit' => 'KILOGRAM',
        ];

        $item = [
            'code'              => 'apollon',
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
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
