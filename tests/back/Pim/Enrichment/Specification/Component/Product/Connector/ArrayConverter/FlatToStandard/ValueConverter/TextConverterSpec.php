<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

class TextConverterSpec extends ObjectBehavior
{
    const TEST_ATTRIBUTE_CODE = 'attribute_code';

    function let(FieldSplitter $fieldSplitter)
    {
        $this->beConstructedWith(
            $fieldSplitter,
            [
                'pim_catalog_identifier',
                'pim_catalog_text',
                'pim_catalog_textarea'
            ]
        );
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(ValueConverterInterface::class);
    }

    function it_supports_converter_field()
    {
        $this->supportsField('pim_catalog_identifier')->shouldReturn(true);
        $this->supportsField('pim_catalog_text')->shouldReturn(true);
        $this->supportsField('pim_catalog_textarea')->shouldReturn(true);
        $this->supportsField('pim_catalog_price')->shouldReturn(false);
    }

    function it_converts_a_string(AttributeInterface $attribute)
    {
        $fieldNameInfo = $this->initFieldNameInfo($attribute);
        $this->convert($fieldNameInfo, 'my awesome text')
            ->shouldReturn($this->initResult('my awesome text'));
    }
    function it_casts_a_number_to_string_during_conversion(AttributeInterface $attribute)
    {
        $fieldNameInfo = $this->initFieldNameInfo($attribute);
        $this->convert($fieldNameInfo, 1234)
            ->shouldReturn($this->initResult('1234'));
    }

    function it_returns_null_data_if_empty_value_provided(AttributeInterface $attribute)
    {
        $fieldNameInfo = $this->initFieldNameInfo($attribute);
        $this->convert($fieldNameInfo, '')
            ->shouldReturn($this->initResult(null));
    }

    function it_throws_a_businessdataarray_exception_with_date_value_input(AttributeInterface $attribute)
    {
        $fieldNameInfo = $this->initFieldNameInfo($attribute);
        $this->shouldThrow(new BusinessArrayConversionException("Can not convert cell  attribute_code with date format to attribute of type text",
            "pim_import_export.notification.export.warnings.xlsx_cell_date_to_text_conversion_error", [self::TEST_ATTRIBUTE_CODE]))
            ->during('convert', [$fieldNameInfo, new \DateTime('2000-01-01')]);
    }

    /**
     * @param AttributeInterface $attribute
     * @return array
     */
    private function initFieldNameInfo(AttributeInterface $attribute): array
    {
        $attribute->getCode()->willReturn(self::TEST_ATTRIBUTE_CODE);
        $attribute->getType()->willReturn('attribute_type');

        $fieldNameInfo = ['attribute' => $attribute, 'locale_code' => 'en_US', 'scope_code' => 'mobile'];
        return $fieldNameInfo;
    }

    /**
     * @param string $str
     * @return \string[][][]
     */
    private function initResult(string $str): array
    {
        return [self::TEST_ATTRIBUTE_CODE => [[
            'locale' => 'en_US',
            'scope' => 'mobile',
            'data' => $str,
        ]]];
    }
}