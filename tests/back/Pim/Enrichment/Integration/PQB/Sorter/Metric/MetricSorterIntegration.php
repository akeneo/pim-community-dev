<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Metric;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Metric sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('product_one', [
            'values' => [
                'a_metric' => [
                    ['data' => ['amount' => '10.55', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_metric' => [
                    ['data' => ['amount' => '15000', 'unit' => 'WATT'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_three', [
            'values' => [
                'a_metric' => [
                    ['data' => ['amount' => '-2.5654', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_metric', Directions::ASCENDING]]);
        $this->assertOrder($result, ['product_three', 'product_one', 'product_two', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_metric', Directions::DESCENDING]]);
        $this->assertOrder($result, ['product_two', 'product_one', 'product_three', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_metric', 'A_BAD_DIRECTION']]);
    }
}
