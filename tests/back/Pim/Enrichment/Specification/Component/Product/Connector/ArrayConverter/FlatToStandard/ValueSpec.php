<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMerger;
use Prophecy\Argument;

class ValueSpec extends ObjectBehavior
{
    function let(
        AttributeColumnInfoExtractor $fieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        ColumnsMerger $columnsMerger
    ) {
        $this->beConstructedWith($fieldExtractor, $converterRegistry, $columnsMerger);
    }

    function it_converts_product_values(
        $fieldExtractor,
        $converterRegistry,
        $columnsMerger,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        AttributeInterface $attribute3,
        AttributeInterface $attribute4,
        AttributeInterface $attribute5,
        AttributeInterface $attribute6,
        AttributeInterface $attribute7,
        ValueConverterInterface $converter
    ) {
        $item = [
            'sku'                    => '1069978',
            '7'                      => 'foo',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price-EUR'              => '15',
            'price-USD'              => '10',
        ];

        $itemMerged = [
            'sku'                    => '1069978',
            '7'                      => 'foo',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price'                  => '15 EUR, 10 USD',
        ];

        $columnsMerger->merge($item)->willReturn($itemMerged);

        $converterRegistry->getConverter(Argument::any())->willReturn($converter);

        $attribute1->getType()->willReturn('sku');
        $attribute2->getType()->willReturn('categories');
        $attribute3->getType()->willReturn('enabled');
        $attribute4->getType()->willReturn('name');
        $attribute5->getType()->willReturn('release_date');
        $attribute6->getType()->willReturn('7');
        $attribute7->getType()->willReturn('price');

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute1]);
        $fieldExtractor->extractColumnInfo('categories')->willReturn(['attribute' => $attribute2]);
        $fieldExtractor->extractColumnInfo('enabled')->willReturn(['attribute' => $attribute3]);
        $fieldExtractor->extractColumnInfo('name')->willReturn(['attribute' => $attribute4]);
        $fieldExtractor->extractColumnInfo('release_date-ecommerce')->willReturn([
            'attribute' => $attribute5,
            'scope_code' => 'ecommerce'
        ]);
        $fieldExtractor->extractColumnInfo('release_date-print')->willReturn([
            'attribute' => $attribute5,
            'scope_code' => 'print'
        ]);
        $fieldExtractor->extractColumnInfo('7')->willReturn(['attribute' => $attribute6]);
        $fieldExtractor->extractColumnInfo('price')->willReturn(['attribute' => $attribute7]);

        $converter->convert(['attribute' => $attribute1], '1069978')->willReturn(
            [
                'sku' => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 1069978,
                    ]
                ]
            ]
        );
        $converter->convert(['attribute' => $attribute2], 'audio_video_sales,loudspeakers,sony')->willReturn(
            ['categories' => ['audio_video_sales', 'loudspeakers', 'sony']]
        );
        $converter->convert(['attribute' => $attribute3], '1')->willReturn(['enabled' => true]);
        $converter->convert(['attribute' => $attribute4], 'Sony SRS-BTV25')->willReturn(
            [
                'name' => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 'Sony SRS-BTV25',
                    ]
                ]
            ]
        );
        $converter->convert(['attribute' => $attribute5, 'scope_code' => 'ecommerce'], '2011-08-21')->willReturn(
            [
                'release_date' => [
                    [
                        'locale' => '',
                        'scope'  => 'ecommerce',
                        'data'   => '2011-08-21'
                    ]
                ]
            ]
        );
        $converter->convert(['attribute' => $attribute5, 'scope_code' => 'print'], '2011-07-15')->willReturn(
            [
                'release_date' => [
                    [
                        'locale' => '',
                        'scope'  => 'print',
                        'data'   => '2011-07-15'
                    ]
                ]
            ]
        );
        $converter->convert(['attribute' => $attribute6], 'foo')->willReturn(
            [
                7 => [
                    [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 'foo'
                    ]
                ]
            ]
        );
        $converter->convert(['attribute' => $attribute7], '15 EUR, 10 USD')->willReturn(
            [
                'price' => [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => [['amount' => 15, 'currency' => 'EUR'], ['amount' => 10, 'currency' => 'USD']]
                ]
            ]
        );

        $result = [
            'sku'          => [
                [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 1069978,
                ]
            ],
            7              => [
                [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 'foo',
                ]
            ],
            'name'         => [
                [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 'Sony SRS-BTV25',
                ]
            ],
            'release_date' => [
                [
                    'locale' => '',
                    'scope'  => 'ecommerce',
                    'data'   => '2011-08-21'
                ],
                [
                    'locale' => '',
                    'scope'  => 'print',
                    'data'   => '2011-07-15'
                ],

            ],
            'price'        => [
                'locale' => '',
                'scope'  => '',
                'data'   => [['amount' => 15, 'currency' => 'EUR'], ['amount' => 10, 'currency' => 'USD']]
            ],
        ];

        $this
            ->convert($item, [])
            ->shouldReturn($result);
    }

    function it_throws_an_exception_if_no_converters_found(
        $converterRegistry,
        $fieldExtractor,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true];

        $columnsMerger->merge($item)->willReturn($item);

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute]);
        $attribute->getType()->willReturn('sku');

        $converterRegistry->getConverter(Argument::any())->willReturn(null);

        $this->shouldThrow(new \LogicException('No converters found for attribute type "sku"'))->during(
            'convert',
            [$item]
        );
    }

    function it_throws_an_exception_if_no_attributes_found($columnsMerger)
    {
        $item = ['sku' => '1069978', 'enabled' => true];

        $columnsMerger->merge($item)->willReturn($item);

        $this->shouldThrow(new \LogicException('Unable to convert the given column "sku"'))->during(
            'convert',
            [$item]
        );
    }
}
