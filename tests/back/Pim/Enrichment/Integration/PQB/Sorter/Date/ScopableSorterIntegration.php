<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Date sorter integration tests for scopable attribute.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_scopable_date', Directions::ASCENDING, ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_scopable_date', Directions::DESCENDING,  ['scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_scopable_date', 'A_BAD_DIRECTION', ['scope' => 'ecommerce']]]);
    }

    /**
     * @jira https://akeneo.atlassian.net/browse/PIM-6872
     */
    public function testSorterWithNoDataOnSorterField()
    {
        $result = $this->executeSorter([['a_scopable_date', Directions::DESCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);

        $result = $this->executeSorter([['a_scopable_date', Directions::ASCENDING, ['scope' => 'ecommerce_china']]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_scopable_date',
            'type'                => AttributeTypes::DATE,
            'localizable'         => false,
            'scopable'            => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_scopable_date' => [
                    ['data' => '2017-04-11', 'locale' => null, 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_scopable_date' => [
                    ['data' => '2016-03-10', 'locale' => null, 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_scopable_date' => [
                    ['data' => '2015-02-09', 'locale' => null, 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }
}
