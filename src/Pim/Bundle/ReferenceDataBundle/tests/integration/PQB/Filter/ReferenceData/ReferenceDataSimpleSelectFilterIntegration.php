<?php

namespace Pim\Bundle\ReferenceDataBundle\tests\integration\PQB\Filter\ReferenceData;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataSimpleSelectFilterIntegration extends AbstractProductQueryBuilderTestCase
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
                        ['data' => 'aero-blue', 'scope' => null, 'locale' => null],
                    ],
                ],
            ]
        );

        $this->createProduct('empty_product', []);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_ref_data_simple_select', Operators::IN_LIST, ['acid-green']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_ref_data_simple_select', Operators::IN_LIST, ['acid-green', 'aero-blue']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_ref_data_simple_select', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_ref_data_simple_select', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_ref_data_simple_select', Operators::NOT_IN_LIST, ['aero-blue']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_ref_data_simple_select', Operators::NOT_IN_LIST, ['acid-green']]]);
        $this->assert($result, ['product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_ref_data_simple_select" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['a_ref_data_simple_select', Operators::IN_LIST, 'string']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "a_ref_data_simple_select" expects a valid code. No reference data "color" with code "NOT_FOUND" has been found, "NOT_FOUND" given.
     */
    public function testErrorOptionNotFound()
    {
        $this->executeFilter([['a_ref_data_simple_select', Operators::IN_LIST, ['NOT_FOUND']]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_ref_data_simple_select" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['a_ref_data_simple_select', Operators::BETWEEN, ['NOT_FOUND']]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
