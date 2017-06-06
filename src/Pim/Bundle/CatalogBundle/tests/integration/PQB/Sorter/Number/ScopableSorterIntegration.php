<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Number;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Number sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'             => 'a_scopable_number',
            'type'             => AttributeTypes::NUMBER,
            'localizable'      => false,
            'scopable'         => true,
            'negative_allowed' => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_scopable_number' => [
                    ['data' => '192.103', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => '16', 'locale' => null, 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_scopable_number' => [
                    ['data' => '16', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => '192.103', 'locale' => null, 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_scopable_number', Directions::ASCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_number', Directions::ASCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_scopable_number', Directions::DESCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_number', Directions::DESCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_scopable_number', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }
}
