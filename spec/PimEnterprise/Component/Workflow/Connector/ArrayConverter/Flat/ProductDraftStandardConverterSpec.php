<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

class ProductDraftStandardConverterSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $productConverter,
        AttributeColumnInfoExtractor $attributeExtractor
    ) {
        $this->beConstructedWith($productConverter, $attributeExtractor);
    }

    function it_converts($attributeExtractor, $productConverter)
    {
        $values = [
            'sku'                      => 'MySku',
            'name-fr_FR'               => 'T-shirt super beau',
            'description-en_US-mobile' => 'My description',
            'length'                   => '10 CENTIMETER'
        ];

        $convertedValues = [
            'sku'   => 'MySku',
            'name'  => [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => 'T-shirt super beau'
            ],
            'description'  => [
                'locale' => 'en_US',
                'scope'  => 'mobile',
                'data'   => 'My description'
            ],
            'length'  => [
                'locale' => null,
                'scope'  => null,
                'data'   => ['data' => 10, 'unit', 'CENTIMETER']
            ],
        ];

        $attributeExtractor->extractColumnInfo('sku')->willReturn(['ciyciy']);
        $attributeExtractor->extractColumnInfo('name-fr_FR')->willReturn([]);
        $attributeExtractor->extractColumnInfo('description-en_US-mobile')->willReturn([]);
        $attributeExtractor->extractColumnInfo('length')->willReturn([]);

        $productConverter->convert($values, [])->willReturn($convertedValues);

        $this->convert($values, [])->shouldReturn($convertedValues);
    }

    function it_filters_field_if_it_is_not_an_attribute($attributeExtractor, $productConverter)
    {
        $values = [
            'sku'                      => 'MySku',
            'name-fr_FR'               => 'T-shirt super beau',
            'description-en_US-mobile' => 'My description',
            'length'                   => '10 CENTIMETER',
            'enabled'                  => 1,
            'categories'               => 'code1,code2'
        ];

        $filteredValues = [
            'sku'                      => 'MySku',
            'name-fr_FR'               => 'T-shirt super beau',
            'description-en_US-mobile' => 'My description',
            'length'                   => '10 CENTIMETER',
        ];

        $convertedValues = [
            'sku'   => 'MySku',
            'name'  => [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => 'T-shirt super beau'
            ],
            'description'  => [
                'locale' => 'en_US',
                'scope'  => 'mobile',
                'data'   => 'My description'
            ],
            'length'  => [
                'locale' => null,
                'scope'  => null,
                'data'   => ['data' => 10, 'unit', 'CENTIMETER'],
            ],
        ];

        $attributeExtractor->extractColumnInfo('sku')->willReturn(['ciyciy']);
        $attributeExtractor->extractColumnInfo('name-fr_FR')->willReturn([]);
        $attributeExtractor->extractColumnInfo('description-en_US-mobile')->willReturn([]);
        $attributeExtractor->extractColumnInfo('length')->willReturn([]);
        $attributeExtractor->extractColumnInfo('enabled')->willReturn(null);
        $attributeExtractor->extractColumnInfo('categories')->willReturn(null);

        $productConverter->convert($filteredValues, [])->willReturn($convertedValues);

        $this->convert($values, [])->shouldReturn($convertedValues);
    }
}
