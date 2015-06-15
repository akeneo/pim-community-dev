<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\FieldConverter;
use Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\ValueConverterInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\ValueConverterRegistryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\Flat\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\Flat\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnsResolver;
use Prophecy\Argument;

class ProductStandardConverterSpec extends ObjectBehavior
{
    function let(
        AttributeColumnInfoExtractor $fieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        AssociationColumnsResolver $assocColumnsResolver,
        AttributeColumnsResolver $attrColumnsResolver,
        FieldConverter $fieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper
    ) {
        $this->beConstructedWith(
            $fieldExtractor,
            $converterRegistry,
            $assocColumnsResolver,
            $attrColumnsResolver,
            $fieldConverter,
            $columnsMerger,
            $columnsMapper
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
        ValueConverterInterface $converter
    ) {
        $item = [
            'sku'                    => '1069978',
            'categories'             => 'audio_video_sales,loudspeakers,sony',
            'enabled'                => '1',
            'name'                   => 'Sony SRS-BTV25',
            'release_date-ecommerce' => '2011-08-21',
        ];

        $columnsMapper->map($item)->willReturn($item);

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku', 'name', 'release_date-ecommerce']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $fieldConverter->supportsColumn('sku')->willReturn(false);
        $fieldConverter->supportsColumn('categories')->willReturn(true);
        $fieldConverter->supportsColumn('enabled')->willReturn(true);
        $fieldConverter->supportsColumn('name')->willReturn(false);
        $fieldConverter->supportsColumn('release_date-ecommerce')->willReturn(false);

        $fieldConverter->convert('categories', 'audio_video_sales,loudspeakers,sony')->willReturn(
            ['categories' => ['audio_video_sales', 'loudspeakers', 'sony']]
        );
        $fieldConverter->convert('enabled', '1')->willReturn(['enabled' => true]);

        $converterRegistry->getConverter(Argument::any())->willReturn($converter);

        $attribute1->getAttributeType()->willReturn('sku');
        $attribute2->getAttributeType()->willReturn('categories');
        $attribute3->getAttributeType()->willReturn('enabled');
        $attribute4->getAttributeType()->willReturn('name');
        $attribute5->getAttributeType()->willReturn('release_date-ecommerce');

        $fieldExtractor->extractColumnInfo('sku')->willReturn(['attribute' => $attribute1]);
        $fieldExtractor->extractColumnInfo('categories')->willReturn(['attribute' => $attribute2]);
        $fieldExtractor->extractColumnInfo('enabled')->willReturn(['attribute' => $attribute3]);
        $fieldExtractor->extractColumnInfo('name')->willReturn(['attribute' => $attribute4]);
        $fieldExtractor->extractColumnInfo('release_date-ecommerce')->willReturn(
            ['attribute' => $attribute5]
        );

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
        $converter->convert(['attribute' => $attribute5], '2011-08-21')->willReturn(
            [
                'release_date-ecommerce' => [
                    [
                        'locale' => '',
                        'scope'  => 'ecommerce',
                        'data'   => '2011-08-21'
                    ]
                ]
            ]
        );

        $result = [
            'sku'                    => [
                'locale' => '',
                'scope'  => '',
                'data'   => 1069978,
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
            'release_date-ecommerce' => [
                [
                    'locale' => '',
                    'scope'  => 'ecommerce',
                    'data'   => '2011-08-21'
                ]
            ]
        ];

        $this
            ->convert($item, [])
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
        $item = ['sku' => '1069978', 'enabled' => 1];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);
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
        $item = ['sku' => '1069978', 'enabled' => 1];

        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);
        $fieldConverter->supportsColumn('sku')->willReturn(false);

        $this->shouldThrow(new \LogicException('Unable to convert the given column "sku"'))->during(
            'convert',
            [$item]
        );
    }
}
