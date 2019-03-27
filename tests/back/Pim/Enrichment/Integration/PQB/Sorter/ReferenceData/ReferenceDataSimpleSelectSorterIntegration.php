<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\ReferenceData;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Sorter for reference data simple select attributes.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataSimpleSelectSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct(
            'product_one',
            [
                'values' => [
                    'a_ref_data_simple_select' => [
                        ['data' => 'acid-green', 'scope' => null, 'locale' => null],
                    ],
                ],
            ]
        );

        $this->createProduct(
            'product_two',
            [
                'values' => [
                    'a_ref_data_simple_select' => [
                        ['data' => 'blue', 'scope' => null, 'locale' => null],
                    ],
                ],
            ]
        );

        $this->createProduct('empty_product', []);
    }

    public function testOperatorAscendant()
    {
        $result = $this->executeSorter([['a_ref_data_simple_select', Directions::ASCENDING]]);
        $this->assert($result, ['product_one', 'product_two', 'empty_product']);
    }

    public function testOperatorDescendant()
    {
        $result = $this->executeSorter([['a_ref_data_simple_select', Directions::DESCENDING]]);
        $this->assert($result, ['product_two', 'product_one', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_ref_data_simple_select', 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
