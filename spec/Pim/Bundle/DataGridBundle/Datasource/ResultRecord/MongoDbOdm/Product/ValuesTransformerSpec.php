<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
            'sku'  => ['code' => 'sku', 'attributeType' => 'text', 'backendType' => 'text'],
            'name' => ['code' => 'name', 'attributeType' => 'text', 'backendType' => 'text'],
            'desc' => ['code' => 'desc', 'attributeType' => 'text', 'backendType' => 'text'],
        ];

        $expected = [
            'normalizedData' => [],
            'sku'  => [
                'attribute' => ['code' => 'sku', 'attributeType' => 'text', 'backendType' => 'text'],
                'locale' => null,
                'scope' => null
            ],
            'name' => [
                'attribute' => ['code' => 'name', 'attributeType' => 'text', 'backendType' => 'text'],
                'locale' => 'fr_FR',
                'scope' => null
            ],
            'desc' => [
                'attribute' => ['code' => 'desc', 'attributeType' => 'text', 'backendType' => 'text'],
                'locale' => 'fr_FR',
                'scope' => 'print'
            ]
        ];

        $this->transform($result, $attributes, $locale, $scope)->shouldReturn($expected);
    }
}
