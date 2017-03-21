<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;

/**
 * @require \MongoId
 */
class ValuesTransformerSpec extends ObjectBehavior
{
    function it_filters_values_by_locale_and_scope_when_it_transforms_result()
    {
        $locale = 'fr_FR';
        $scope = 'print';

        $result = [
            'values' => [
                ['attribute' => 'sku', 'locale' => null, 'scope' => null],
                ['attribute' => 'name', 'locale' => 'fr_FR', 'scope' => null],
                ['attribute' => 'name', 'locale' => 'en_US', 'scope' => null],
                ['attribute' => 'desc', 'locale' => 'fr_FR', 'scope' => 'mobile'],
                ['attribute' => 'desc', 'locale' => 'en_US', 'scope' => 'mobile'],
                ['attribute' => 'desc', 'locale' => 'fr_FR', 'scope' => 'print'],
                ['attribute' => 'desc', 'locale' => 'en_US', 'scope' => 'print'],
            ],
            'normalizedData' => []
        ];

        $attributes = [
            'sku'  => ['code' => 'sku', 'type' => 'text', 'backendType' => 'text', 'properties' => []],
            'name' => ['code' => 'name', 'type' => 'text', 'backendType' => 'text', 'properties' => []],
            'desc' => ['code' => 'desc', 'type' => 'text', 'backendType' => 'text', 'properties' => []],
        ];

        $expected = [
            'normalizedData' => [],
            'sku'  => [
                'attribute' => ['code' => 'sku', 'type' => 'text', 'backendType' => 'text', 'properties' => []],
                'locale' => null,
                'scope' => null
            ],
            'name' => [
                'attribute' => ['code' => 'name', 'type' => 'text', 'backendType' => 'text', 'properties' => []],
                'locale' => 'fr_FR',
                'scope' => null
            ],
            'desc' => [
                'attribute' => ['code' => 'desc', 'type' => 'text', 'backendType' => 'text', 'properties' => []],
                'locale' => 'fr_FR',
                'scope' => 'print'
            ]
        ];

        $this->transform($result, $attributes, $locale, $scope)->shouldReturn($expected);
    }
}
