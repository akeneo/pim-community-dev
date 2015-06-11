<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat;

use PhpSpec\ObjectBehavior;

class AttributeStandardConverterSpec extends ObjectBehavior
{
    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface'
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
            'attributeType'          => "pim_catalog_identifier",
            'code'                   => "sku",
            'group'                  => "marketing",
            'unique'                 => true,
            'useable_as_grid_filter' => true,
            'allowed_extensions'     => "",
            'metric_family'          => "",
            'default_metric_unit'    => "",
            'reference_data_name'    => "",
            'localizable'            => false,
            'scopable'               => false,
        ];

        $this->convert($item)->shouldReturn($result);
    }
}
