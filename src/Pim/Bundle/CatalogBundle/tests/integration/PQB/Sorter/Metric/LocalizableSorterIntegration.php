<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Metric;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Metric sorter integration tests for localizable attribute
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_metric',
            'type'                => AttributeTypes::METRIC,
            'localizable'         => true,
            'scopable'            => false,
            'decimals_allowed'    => true,
            'metric_family'       => 'Power',
            'default_metric_unit' => 'WATT'
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => '10.55', 'unit' => 'KILOWATT'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => '15000', 'unit' => 'WATT'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => '-2.5654', 'unit' => 'KILOWATT'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_metric', Directions::ASCENDING, ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_three', 'product_one', 'product_two', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_metric', Directions::DESCENDING,  ['locale' => 'fr_FR']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_localizable_metric', 'A_BAD_DIRECTION', ['locale' => 'fr_FR']]]);
    }
}
