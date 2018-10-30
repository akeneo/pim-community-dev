<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;

class ColumnsMapperSpec extends ObjectBehavior
{
    function it_maps_source_and_destination_columns_name()
    {
        $row = [
            'sku' => 'mysku',
            'famille' => 'myfamilycode',
            'cats' => 'mycatcode1,mycatcode2'
        ];
        $mapping = [
            'famille' => 'family',
            'cats' => 'categories'
        ];
        $resultRow = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];

        $this->map($row, $mapping)->shouldReturn($resultRow);
    }

    function it_does_not_map_when_no_mapping_is_provided()
    {
        $row = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];
        $mapping = [];
        $resultRow = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];

        $this->map($row, $mapping)->shouldReturn($resultRow);
    }

    function it_does_not_map_when_source_and_destination_are_the_same()
    {
        $row = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];
        $mapping = ['family' => 'family'];
        $resultRow = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];

        $this->map($row, $mapping)->shouldReturn($resultRow);
    }
}
