<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Metric;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_metric']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_metric' => [
                    ['data' => ['amount' => '10.55', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_metric' => [
                    ['data' => ['amount' => '15', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_metric', Operators::LOWER_THAN, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_THAN, ['amount' => 10.5501, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_THAN, ['amount' => 16, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_THAN, ['amount' => 10550, 'unit' => 'WATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_THAN, ['amount' => 10550.1, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_THAN, ['amount' => 16000, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.4999, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10499.9, 'unit' => 'WATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10550, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 15000, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_metric', Operators::EQUALS, ['amount' => 10.5501, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::EQUALS, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_metric', Operators::EQUALS, ['amount' => 10550.1, 'unit' => 'WATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::EQUALS, ['amount' => 10550, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_metric', Operators::GREATER_THAN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_THAN, ['amount' => 10.4999, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_THAN, ['amount' => 15000, 'unit' => 'WATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_THAN, ['amount' => 10499.9, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15.01, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15010, 'unit' => 'WATT']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 10550, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15000, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_metric', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_metric', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_metric', Operators::NOT_EQUAL, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_metric', Operators::NOT_EQUAL, ['amount' => 15000, 'unit' => 'WATT']]]);
        $this->assert($result, ['product_one']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_metric" expects an array as data, "string" given.');

        $this->executeFilter([['a_metric', Operators::NOT_EQUAL, 'string']]);
    }

    public function testErrorAmountIsMissing()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_metric" expects an array with the key "amount".');

        $this->executeFilter([['a_metric', Operators::NOT_EQUAL, ['unit' => 'WATT']]]);
    }

    public function testErrorCurrencyIsMissing()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_metric" expects an array with the key "unit".');

        $this->executeFilter([['a_metric', Operators::NOT_EQUAL, ['amount' => '']]]);
    }

    public function testErrorUnitNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('The unit does not exist in the attribute\'s family "Power"');

        $this->executeFilter([['a_metric', Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'NOT_FOUND']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_metric" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['a_metric', Operators::BETWEEN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
    }
}
