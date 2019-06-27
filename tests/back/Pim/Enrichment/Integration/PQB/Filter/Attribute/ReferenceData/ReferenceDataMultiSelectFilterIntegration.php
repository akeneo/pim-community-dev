<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\ReferenceData;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataMultiSelectFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_ref_data_multi_select']
        ]);

        $this->createProduct(
            'product_one',
            [
                'family' => 'a_family',
                'values' => [
                    'a_ref_data_multi_select' => [
                        [
                            'data'   => [
                                'aertex',
                                'ballisticnylon',
                            ],
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ]
        );

        $this->createProduct(
            'product_two',
            [
                'family' => 'a_family',
                'values' => [
                    'a_ref_data_multi_select' => [
                        [
                            'data'   => [
                                'argentanlace',
                                'ballisticnylon',
                            ],
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ]
        );

        $this->createProduct(
            'product_three',
            [
                'family' => 'a_family',
                'values' => [
                    'a_ref_data_multi_select' => [
                        [
                            'data'   => [
                                'betacloth',
                                'bobbinet',
                            ],
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ]
        );

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_ref_data_multi_select', Operators::IN_LIST, ['aertex']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_ref_data_multi_select', Operators::IN_LIST, ['aertex', 'ballisticnylon']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_ref_data_multi_select', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_ref_data_multi_select', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two', 'product_three']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_ref_data_multi_select', Operators::NOT_IN_LIST, ['ballisticnylon']]]);
        $this->assert($result, ['product_three']);

        $result = $this->executeFilter([['a_ref_data_multi_select', Operators::NOT_IN_LIST, ['bobbinet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_ref_data_multi_select" expects an array as data, "string" given.');

        $this->executeFilter([['a_ref_data_multi_select', Operators::IN_LIST, 'string']]);
    }

    public function testErrorOptionNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "a_ref_data_multi_select" expects a valid code. No reference data "fabrics" with code "NOT_FOUND" has been found, "NOT_FOUND" given.');

        $this->executeFilter([['a_ref_data_multi_select', Operators::IN_LIST, ['NOT_FOUND']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_ref_data_multi_select" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['a_ref_data_multi_select', Operators::BETWEEN, ['NOT_FOUND']]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
