<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ProductFieldConverter;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ValueConverterInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ValueConverterRegistryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Extractor\ProductAttributeFieldExtractor;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Mapper\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Merger\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AssociationFieldsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AttributeFieldsResolver;
use Prophecy\Argument;

class ProductStandardConverterSpec extends ObjectBehavior
{
    function let(
        ProductAttributeFieldExtractor $fieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        AssociationFieldsResolver $assocFieldsResolver,
        AttributeFieldsResolver $attrFieldsResolver,
        ProductFieldConverter $productFieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper
    ) {
        $this->beConstructedWith(
            $fieldExtractor,
            $converterRegistry,
            $assocFieldsResolver,
            $attrFieldsResolver,
            $productFieldConverter,
            $columnsMerger,
            $columnsMapper
        );
    }

    function it_converts(
        $fieldExtractor,
        $productFieldConverter,
        $converterRegistry,
        $columnsMerger,
        $columnsMapper,
        $attrFieldsResolver,
        $assocFieldsResolver,
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

        $attrFieldsResolver->resolveAttributesFields()->willReturn(['sku', 'name', 'release_date-ecommerce']);
        $assocFieldsResolver->resolveAssociationFields()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);

        $productFieldConverter->supportsColumn('sku')->willReturn(false);
        $productFieldConverter->supportsColumn('categories')->willReturn(true);
        $productFieldConverter->supportsColumn('enabled')->willReturn(true);
        $productFieldConverter->supportsColumn('name')->willReturn(false);
        $productFieldConverter->supportsColumn('release_date-ecommerce')->willReturn(false);

        $productFieldConverter->convert('categories', 'audio_video_sales,loudspeakers,sony')->willReturn(
            ['categories' => ['audio_video_sales', 'loudspeakers', 'sony']]
        );
        $productFieldConverter->convert('enabled', '1')->willReturn(['enabled' => true]);

        $converterRegistry->getConverter(Argument::any())->willReturn($converter);

        $attribute1->getAttributeType()->willReturn('sku');
        $attribute2->getAttributeType()->willReturn('categories');
        $attribute3->getAttributeType()->willReturn('enabled');
        $attribute4->getAttributeType()->willReturn('name');
        $attribute5->getAttributeType()->willReturn('release_date-ecommerce');

        $fieldExtractor->extractAttributeFieldNameInfos('sku')->willReturn(['attribute' => $attribute1]);
        $fieldExtractor->extractAttributeFieldNameInfos('categories')->willReturn(['attribute' => $attribute2]);
        $fieldExtractor->extractAttributeFieldNameInfos('enabled')->willReturn(['attribute' => $attribute3]);
        $fieldExtractor->extractAttributeFieldNameInfos('name')->willReturn(['attribute' => $attribute4]);
        $fieldExtractor->extractAttributeFieldNameInfos('release_date-ecommerce')->willReturn(
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
        $attrFieldsResolver,
        $assocFieldsResolver,
        $productFieldConverter,
        $converterRegistry,
        $fieldExtractor,
        $columnsMerger,
        AttributeInterface $attribute
    ) {
        $item = ['sku' => '1069978', 'enabled' => 1];

        $attrFieldsResolver->resolveAttributesFields()->willReturn(['sku']);
        $assocFieldsResolver->resolveAssociationFields()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);
        $fieldExtractor->extractAttributeFieldNameInfos('sku')->willReturn(['attribute' => $attribute]);
        $attribute->getAttributeType()->willReturn('sku');

        $productFieldConverter->supportsColumn('sku')->willReturn(false);

        $converterRegistry->getConverter(Argument::any())->willReturn(null);

        $this->shouldThrow(new \LogicException('No converters found for attribute type "sku"'))->during(
            'convert',
            [$item]
        );
    }

    function it_throws_an_exception_if_no_attributes_found(
        $attrFieldsResolver,
        $assocFieldsResolver,
        $columnsMerger,
        $productFieldConverter
    ) {
        $item = ['sku' => '1069978', 'enabled' => 1];

        $attrFieldsResolver->resolveAttributesFields()->willReturn(['sku']);
        $assocFieldsResolver->resolveAssociationFields()->willReturn([]);

        $columnsMerger->merge($item)->willReturn($item);
        $productFieldConverter->supportsColumn('sku')->willReturn(false);

        $this->shouldThrow(new \LogicException('Unable to convert the given column "sku"'))->during(
            'convert',
            [$item]
        );
    }
}
