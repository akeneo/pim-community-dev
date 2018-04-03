<?php

namespace Pim\Bundle\ReferenceDataBundle\tests\integration\PQB\Sorter\ReferenceData;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

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
    protected function setUp()
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

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
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
