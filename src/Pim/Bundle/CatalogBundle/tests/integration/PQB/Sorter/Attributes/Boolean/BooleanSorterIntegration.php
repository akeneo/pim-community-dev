<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Metric;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Boolean sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('yes', [
                'values' => [
                    'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('no', [
                'values' => [
                    'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_yes_no', Directions::ASCENDING]]);
        $this->assertOrder($result, ['no', 'yes', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_yes_no', Directions::DESCENDING]]);
        $this->assertOrder($result, ['yes', 'no', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_yes_no', 'A_BAD_DIRECTION']]);
    }
}
