<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Date;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Date sorter integration tests.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_date', Directions::ASCENDING]]);
        $this->assertOrder($result, ['product_three', 'product_two', 'product_one', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_date', Directions::DESCENDING]]);
        $this->assertOrder($result, ['product_one', 'product_two', 'product_three', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_date', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_one', [
            'values' => [
                'a_date' => [
                    ['data' => '2017-04-11', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_date' => [
                    ['data' => '2016-03-10', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_date' => [
                    ['data' => '2015-02-09', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }
}
