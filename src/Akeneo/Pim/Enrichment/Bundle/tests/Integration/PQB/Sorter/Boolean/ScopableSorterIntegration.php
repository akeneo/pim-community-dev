<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Boolean;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Boolean sorter integration tests for scopable attribute
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
            'code'                => 'a_scopable_yes_no',
            'type'                => AttributeTypes::BOOLEAN,
            'localizable'         => false,
            'scopable'            => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_scopable_yes_no' => [
                    ['data' => false, 'scope' => 'ecommerce', 'locale' => null],
                    ['data' => true, 'scope' => 'tablet', 'locale' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_scopable_yes_no' => [
                    ['data' => true, 'scope' => 'ecommerce', 'locale' => null],
                    ['data' => false, 'scope' => 'tablet', 'locale' => null],
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_scopable_yes_no', Directions::ASCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_yes_no', Directions::ASCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_scopable_yes_no', Directions::DESCENDING,  ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_yes_no', Directions::DESCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_scopable_yes_no', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_scopable_yes_no', Directions::DESCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_yes_no', Directions::ASCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }
}
