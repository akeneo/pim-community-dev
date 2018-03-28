<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Number;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Number sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_one', [
            'values' => [
                'a_number_float_negative' => [
                    ['data' => '192.103', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_number_float_negative' => [
                    ['data' => '16', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_number_float_negative' => [
                    ['data' => '-162.5654', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_number_float_negative', Directions::ASCENDING]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_number_float_negative', Directions::DESCENDING]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_number_float_negative', 'A_BAD_DIRECTION']]);
    }
}
