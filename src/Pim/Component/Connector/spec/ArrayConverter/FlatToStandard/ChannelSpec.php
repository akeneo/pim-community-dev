<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class ChannelSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\ArrayConverterInterface'
        );
    }

    function it_converts_an_item_to_standard_format()
    {
        $item = [
            'code'        => 'ecommerce',
            'label-fr_FR' => 'Ecommerce',
            'label-en_US' => 'Ecommerce',
            'locales'     => 'en_US,fr_FR',
            'currencies'  => 'EUR,USD',
            'tree'        => 'master_catalog',
            'conversion_units' => 'weight: GRAM,maximum_scan_size:KILOMETER, display_diagonal:DEKAMETER, viewing_area: DEKAMETER'
        ];

        $result = [
            'labels'           => [
                'fr_FR' => 'Ecommerce',
                'en_US' => 'Ecommerce',
            ],
            'code'             => 'ecommerce',
            'locales'          => ['en_US', 'fr_FR'],
            'currencies'       => ['EUR', 'USD'],
            'category_tree'    => 'master_catalog',
            'conversion_units' => [
                'weight'            => 'GRAM',
                'maximum_scan_size' => 'KILOMETER',
                'display_diagonal'  => 'DEKAMETER',
                'viewing_area'      => 'DEKAMETER'
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }

    function it_converts_empty_conversion_units()
    {
        $this->convert(['conversion_units' => ''])->shouldReturn(['labels' => [], 'conversion_units' => []]);
    }
}
