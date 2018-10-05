<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class AttributeSpec extends ObjectBehavior
{
    const TEST_TIMEZONE = 'Europe/Paris';

    protected $userTimezone;

    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->userTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);

        $booleanFields = [
            'localizable',
            'useable_as_grid_filter',
            'unique',
            'required',
            'scopable',
            'wysiwyg_enabled',
            'decimals_allowed',
            'negative_allowed',
        ];
        $this->beConstructedWith($fieldChecker, $booleanFields);
    }

    function letGo()
    {
        date_default_timezone_set($this->userTimezone);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            ArrayConverterInterface::class
        );
    }

    function it_converts_an_item_to_standard_format()
    {
        $item = [
            'type'                   => 'pim_catalog_identifier',
            'code'                   => 'sku',
            'label-de_DE'            => 'SKU',
            'label-en_US'            => 'SKU',
            'label-fr_FR'            => 'SKU',
            'group'                  => 'marketing',
            'unique'                 => '1',
            'useable_as_grid_filter' => '1',
            'allowed_extensions'     => '',
            'metric_family'          => '',
            'default_metric_unit'    => '',
            'reference_data_name'    => 'color',
            'localizable'            => '0',
            'scopable'               => '0',
        ];

        $result = [
            'labels'                 => [
                'de_DE' => 'SKU',
                'en_US' => 'SKU',
                'fr_FR' => 'SKU',
            ],
            'type'                   => 'pim_catalog_identifier',
            'code'                   => 'sku',
            'group'                  => 'marketing',
            'unique'                 => true,
            'useable_as_grid_filter' => true,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => 'color',
            'localizable'            => false,
            'scopable'               => false,
        ];

        $this->convert($item)->shouldReturn($result);
    }

    function it_does_not_convert_empty_reference_data_name()
    {
        $item = [
            'type'                   => 'pim_catalog_identifier',
            'code'                   => 'sku',
            'label-de_DE'            => 'SKU',
            'label-en_US'            => 'SKU',
            'label-fr_FR'            => 'SKU',
            'group'                  => 'marketing',
            'unique'                 => '1',
            'useable_as_grid_filter' => '1',
            'allowed_extensions'     => '',
            'metric_family'          => '',
            'default_metric_unit'    => '',
            'reference_data_name'    => '',
            'localizable'            => '0',
            'scopable'               => '0',
        ];

        $result = [
            'labels'                 => [
                'de_DE' => 'SKU',
                'en_US' => 'SKU',
                'fr_FR' => 'SKU',
            ],
            'type'                   => 'pim_catalog_identifier',
            'code'                   => 'sku',
            'group'                  => 'marketing',
            'unique'                 => true,
            'useable_as_grid_filter' => true,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'localizable'            => false,
            'scopable'               => false,
        ];

        $this->convert($item)->shouldReturn($result);
    }

    function it_fills_options_only_when_not_blank()
    {
        $itemBlank = [
            'type'           => 'pim_catalog_integer',
            'code'           => 'num',
            'number_min'     => '',
            'number_max'     => '',
        ];
        $itemFilled = [
            'type'           => 'pim_catalog_integer',
            'code'           => 'num',
            'number_min'     => '12',
            'number_max'     => '15',
        ];
        $this->convert($itemBlank)->shouldReturn([
            'labels'         => [],
            'type'           => 'pim_catalog_integer',
            'code'           => 'num',
            'number_min'     => null,
            'number_max'     => null,
        ]);
        $this->convert($itemFilled)->shouldReturn([
            'labels'         => [],
            'type'           => 'pim_catalog_integer',
            'code'           => 'num',
            'number_min'     => '12.0000',
            'number_max'     => '15.0000',
        ]);
    }

    function it_does_not_convert_empty_keys()
    {
        $item = [
            '' => 'foo',
            0 => 'bar',
        ];

        $result = [
            'labels' => [],
            '' => 'foo',
            0 => 'bar',
        ];

        $this->convert($item)->shouldReturn($result);
    }

    function it_converts_a_date()
    {
        $this->convert(['date_min' => '2015-01-31'])->shouldReturn([
            'labels'   => [],
            'date_min' => '2015-01-31T00:00:00+01:00'
        ]);
    }

    function it_does_not_convert_a_date()
    {
        $this->convert(['date_min' => '2015-45-31'])->shouldReturn([
            'labels'   => [],
            'date_min' => '2015-45-31'
        ]);

        $this->convert(['date_min' => '2015/10/31'])->shouldReturn([
            'labels'   => [],
            'date_min' => '2015/10/31'
        ]);

        $this->convert(['date_min' => 'not a date'])->shouldReturn([
            'labels'   => [],
            'date_min' => 'not a date'
        ]);
    }
}
