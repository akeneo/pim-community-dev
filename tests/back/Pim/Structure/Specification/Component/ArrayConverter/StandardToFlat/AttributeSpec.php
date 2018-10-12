<?php

namespace Specification\Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    function let()
    {
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

        $this->beConstructedWith($booleanFields);
    }

    function it_converts_from_standard_to_flat_format()
    {
        $expected = [
            'label-fr_FR'            => 'La description',
            'label-en_US'            => 'The description',
            'type'                   => 'pim_catalog_text',
            'number_min'             => '23.5',
            'number_max'             => '29.9',
            'max_file_size'          => '3500',
            'sort_order'             => '5',
            'max_characters'         => '8000',
            'minimum_input_length'   => '',
            'options'                => 'blue,red,yellow',
            'available_locales'      => '',
            'date_min'               => '',
            'date_max'               => '2013-02-22',
            'reference_data_name'    => '',
            'localizable'            => '0',
            'useable_as_grid_filter' => '0',
            'unique'                 => '0',
            'required'               => '0',
            'scopable'               => '0',
            'wysiwyg_enabled'        => '0',
            'decimals_allowed'       => '1',
            'negative_allowed'       => '1',
        ];

        $item = [
            'labels'                 => [
                'fr_FR' => 'La description',
                'en_US' => 'The description',
            ],
            'type'                   => 'pim_catalog_text',
            'number_min'             => 23.5,
            'number_max'             => 29.9,
            'max_file_size'          => 3500,
            'sort_order'             => 5,
            'max_characters'         => 8000,
            'minimum_input_length'   => null,
            'options'                => [
                'blue',
                'red',
                'yellow',
            ],
            'available_locales'      => [],
            'date_min'               => null,
            'date_max'               => '2013-02-22',
            'reference_data_name'    => null,
            'localizable'            => false,
            'useable_as_grid_filter' => false,
            'unique'                 => false,
            'required'               => false,
            'scopable'               => false,
            'wysiwyg_enabled'        => false,
            'decimals_allowed'       => true,
            'negative_allowed'       => true,
        ];

        $this->convert($item)->shouldReturn($expected);
    }

    function it_converts_from_standard_to_flat_format_with_null_values()
    {
        $expected = [
            'wysiwyg_enabled'  => '',
            'decimals_allowed' => '',
            'negative_allowed' => '',
        ];

        $item = [
            'wysiwyg_enabled'  => null,
            'decimals_allowed' => null,
            'negative_allowed' => null,
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
