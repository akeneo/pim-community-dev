<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldConverter;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterRegistryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\Exception\StructureArrayConversionException;
use Prophecy\Argument;

class ProductSpec extends ObjectBehavior
{
    function let(
        AttributeColumnInfoExtractor $fieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        AssociationColumnsResolver $assocColumnsResolver,
        AttributeColumnsResolver $attrColumnsResolver,
        FieldConverter $fieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper,
        FieldsRequirementChecker $fieldChecker
    ) {
        $this->beConstructedWith(
            $fieldExtractor,
            $converterRegistry,
            $assocColumnsResolver,
            $attrColumnsResolver,
            $fieldConverter,
            $columnsMerger,
            $columnsMapper,
            $fieldChecker
        );
    }

    function it_converts(
        $fieldExtractor,
        $fieldConverter,
        $converterRegistry,
        $columnsMerger,
        $columnsMapper,
        $attrColumnsResolver,
        $assocColumnsResolver,
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
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price-EUR'              => '15',
            'price-USD'              => '10',
            'X_SELL-groups'          => 'group-A',
            'X_SELL-products'        => 'sku-A, sku-B'
        ];

        $itemMerged = [
            'sku'                    => '1069978',
            '7'                      => 'foo',
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
            'release_date-print'     => '2011-07-15',
            'price'                  => '15 EUR, 10 USD',
            'X_SELL-groups'          => 'group-A',
            'X_SELL-products'        => 'sku-A, sku-B'
        ];

        $columnsMapper->map($item)->willReturn($item);

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(
            ['sku', 'name', 'release_date-ecommerce', 'release_date-print', 7, 'price', 'price-EUR', 'price-USD']
        );
        $assocColumnsResolver->resolveAssociationColumns()->willReturn(['X_SELL-groups', 'X_SELL-products']);

        $columnsMerger->merge($item)->willReturn($itemMerged);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldConverter->supportsColumn('sku')->willReturn(false);
        $fieldConverter->supportsColumn('categories')->willReturn(true);
        $fieldConverter->supportsColumn('enabled')->willReturn(true);
        $fieldConverter->supportsColumn('name')->willReturn(false);
        $fieldConverter->supportsColumn('release_date-ecommerce')->willReturn(false);
        $fieldConverter->supportsColumn('release_date-print')->willReturn(false);
        $fieldConverter->supportsColumn('7')->willReturn(false);
        $fieldConverter->supportsColumn('price')->willReturn(false);
        $fieldConverter->supportsColumn('X_SELL-groups')->willReturn(true);
        $fieldConverter->supportsColumn('X_SELL-products')->willReturn(true);

        $fieldConverter->convert('categories', 'audio_video_sales,loudspeakers,sony')->willReturn(
            ['categories' => ['audio_video_sales', 'loudspeakers', 'sony']]
        );
        $fieldConverter->convert('enabled', '1')->willReturn(['enabled' => true]);
        $fieldConverter->convert('X_SELL-groups', 'group-A')->willReturn(
            ['associations' => ['X_SELL' => ['groups' => ['group-A']]]]
        );
        $fieldConverter->convert('X_SELL-products', 'sku-A, sku-B')->willReturn(
            ['associations' => ['X_SELL' => ['products' => ['sku-A', 'sku-B']]]]
        );

        $converterRegistry->getConverter(Argument::any())->willReturn($converter);

        $attribute1->getAttributeType()->willReturn('sku');
        $attribute2->getAttributeType()->willReturn('categories');
        $attribute3->getAttributeType()->willReturn('enabled');
        $attribute4->getAttributeType()->willReturn('name');
        $attribute5->getAttributeType()->willReturn('release_date');
        $attribute6->getAttributeType()->willReturn('7');
        $attribute7->getAttributeType()->willReturn('price');

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
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 1069978,
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
                        'locale' => '',
                        'scope'  => '',
                        'data'   => 'foo'
                ]
            ]
        );
        $converter->convert(['attribute' => $attribute7], '15 EUR, 10 USD')->willReturn(
            [
                'price' => [
                        'locale' => '',
                        'scope'  => '',
                        'data'   => [['data' => 15, 'currency' => 'EUR'], ['data' => 10, 'currency' => 'USD']]
                ]
            ]
        );

        $result = [
            'sku'                    => [
                'locale' => '',
                'scope'  => '',
                'data'   => 1069978,
            ],
            7                        => [
                'locale' => '',
                'scope'  => '',
                'data'   => 'foo',
            ],
            'categories'             => ['audio_video_sales', 'loudspeakers', 'sony'],
            'enabled'                => true,
            'name'                   => [
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
            'price' => [
                'locale' => '',
                'scope'  => '',
                'data'   => [['data' => 15, 'currency' => 'EUR'], ['data' => 10, 'currency' => 'USD']]
            ],
            'associations' => [
                'X_SELL' => [
                    'groups' => ['group-A'],
                    'products' => ['sku-A', 'sku-B'],
                ],
            ]
        ];

        $this
            ->convert($item, [])
            ->shouldReturn($result);
    }

    function it_converts_without_associations_depending_on_options(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $converterRegistry,
        $fieldExtractor,
        $columnsMerger,
        ValueConverterInterface $converter,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true, 'unknown-products' => ['sku2'], 'unknown-groups' => 'groupcode'];
        $filteredItem = ['sku' => '1069978', 'enabled' => true];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($filteredItem)->willReturn($filteredItem);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute]);
        $attribute->getAttributeType()->willReturn('sku');
        $fieldConverter->supportsColumn('sku')->willReturn(false);
        $fieldConverter->supportsColumn('enabled')->willReturn(true);

        $converterRegistry->getConverter(Argument::any())->willReturn($converter);
        $fieldConverter->convert('enabled', true)->willReturn(['enabled' => true]);

        $converter->convert(['attribute' => $attribute], '1069978')->willReturn(
            [
                'sku' => [
                    'locale' => '',
                    'scope'  => '',
                    'data'   => 1069978,
                ]
            ]
        );

        $result = [
            'sku'                    => [
                'locale' => '',
                'scope'  => '',
                'data'   => 1069978,
            ],
            'enabled'                => true,
        ];

        $this
            ->convert($item, ['with_associations' => false])
            ->shouldReturn($result);
    }

    function it_throws_an_exception_if_no_converters_found(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $converterRegistry,
        $fieldExtractor,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute]);
        $attribute->getAttributeType()->willReturn('sku');

        $fieldConverter->supportsColumn('sku')->willReturn(false);

        $converterRegistry->getConverter(Argument::any())->willReturn(null);

        $this->shouldThrow(new \LogicException('No converters found for attribute type "sku"'))->during(
            'convert',
            [$item]
        );
    }

    function it_throws_an_exception_if_no_attributes_found(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $columnsMerger,
        $fieldConverter
    ) {
        $item = ['sku' => '1069978', 'enabled' => true];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldConverter->supportsColumn('sku')->willReturn(false);

        $this->shouldThrow(new \LogicException('Unable to convert the given column "sku"'))->during(
            'convert',
            [$item]
        );
    }

    function it_throws_an_exception_when_field_does_not_exist(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $converterRegistry,
        $fieldExtractor,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true, 'unknown_field' => 'foo', 'other_unknown_field' => 'bar'];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute]);
        $attribute->getAttributeType()->willReturn('sku');
        $fieldConverter->supportsColumn('sku')->willReturn(false);

        $fieldExtractor->extractColumnInfo('unknown_field')->willReturn(null);
        $fieldExtractor->extractColumnInfo('other_unknown_field')->willReturn(null);

        $converterRegistry->getConverter(Argument::any())->willReturn(null);

        $this->shouldThrow(
            new StructureArrayConversionException('The fields "unknown_field, other_unknown_field" do not exist')
        )->during(
            'convert',
            [$item],
            ['with_associations' => false]
        );
    }

    function it_throws_an_exception_when_association_field_does_not_exist(
        $attrColumnsResolver,
        $assocColumnsResolver,
        $fieldConverter,
        $converterRegistry,
        $fieldExtractor,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => true, 'unknown-products' => ['sku2'], 'unknown-groups' => 'groupcode'];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute]);
        $attribute->getAttributeType()->willReturn('sku');

        $fieldConverter->supportsColumn('sku')->willReturn(true);

        $fieldExtractor->extractColumnInfo('unknown_field')->willReturn(null);
        $fieldExtractor->extractColumnInfo('other_unknown_field')->willReturn(null);

        $converterRegistry->getConverter(Argument::any())->willReturn(null);

        $this->shouldThrow(
            new StructureArrayConversionException('The fields "unknown-products, unknown-groups" do not exist')
        )->during(
            'convert',
            [$item],
            ['with_associations' => true]
        );
    }
}
