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
            'code'                    => 'apollon',
            'categories'              => 'audio_video_sales,loudspeakers,sony',
            'family_variant'          => 'soundspeaker_color',
            'parent'                  => 'parent_model_code',
            'weight-de_DE-print'      => '100',
            'weight-de_DE-print-unit' => 'KILOGRAM',
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
            ]
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
