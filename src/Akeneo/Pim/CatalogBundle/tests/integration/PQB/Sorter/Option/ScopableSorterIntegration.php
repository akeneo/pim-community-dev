<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Option;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Scopable option attribute sorter integration tests (simple select)
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
            'code'        => 'a_select_scopable_simple_select',
            'type'        => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => false,
            'scopable'    => true,
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_select_scopable_simple_select',
            'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_select_scopable_simple_select',
            'code'      => 'black'
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_select_scopable_simple_select' => [
                    ['data' => 'black', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'orange', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_select_scopable_simple_select' => [
                    ['data' => 'orange', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'black', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_select_scopable_simple_select', Directions::ASCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_select_scopable_simple_select', Directions::ASCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_select_scopable_simple_select', Directions::DESCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'empty_product']);

        $result = $this->executeSorter([['a_select_scopable_simple_select', Directions::DESCENDING, ['scope' => 'tablet']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_select_scopable_simple_select', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_select_scopable_simple_select', Directions::DESCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);

        $result = $this->executeSorter([['a_select_scopable_simple_select', Directions::ASCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'empty_product']);
    }
}
