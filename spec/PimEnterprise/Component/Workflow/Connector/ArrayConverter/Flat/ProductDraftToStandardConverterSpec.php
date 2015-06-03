<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Extractor\ProductAttributeFieldExtractor;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

class ProductDraftToStandardConverterSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $productConverter,
        ProductAttributeFieldExtractor $attributeExtractor
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


        $attributeExtractor->extractAttributeFieldNameInfos('sku')->willReturn(['ciyciy']);
        $attributeExtractor->extractAttributeFieldNameInfos('name-fr_FR')->willReturn([]);
        $attributeExtractor->extractAttributeFieldNameInfos('description-en_US-mobile')->willReturn([]);
        $attributeExtractor->extractAttributeFieldNameInfos('length')->willReturn([]);

        $productConverter->convert($values, [])->willReturn($convertedValues);

        $this->convert($values, [])->shouldReturn($convertedValues);
    }

    function it_throws_an_exception_if_there_is_other_field_than_attribute()
    {
        $this->shouldThrow(new \LogicException('Field "enable" is not allowed. Only attributes are allowed in a product draft'))->during(
            'convert',
            [['enable' => 1]]
        );
    }
}
