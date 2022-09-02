<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Number;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_number_float_negative']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetNumberValue('a_number_float_negative', null, null, -15.5),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetNumberValue('a_number_float_negative', null, null, 19.0),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_THAN, -15.5]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_THAN, -15.4999]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_THAN, 19.0001]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_THAN, '19.0001']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_OR_EQUAL_THAN, -15.5]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_OR_EQUAL_THAN, 19]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::LOWER_OR_EQUAL_THAN, '19']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::EQUALS, 15.5]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_number_float_negative', Operators::EQUALS, -15.5]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::EQUALS, '-15.5']]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_THAN, -15.5]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_THAN, -15.5001]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_THAN, '-15.5001']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_OR_EQUAL_THAN, -15.5]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_OR_EQUAL_THAN, 19]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_OR_EQUAL_THAN, 19.0001]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_number_float_negative', Operators::GREATER_OR_EQUAL_THAN, '19']]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::IS_EMPTY, 0]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::IS_NOT_EMPTY, 0]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_number_float_negative', Operators::NOT_EQUAL, '15.5']]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::NOT_EQUAL, '-15.5']]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::NOT_EQUAL, 15.5]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_number_float_negative', Operators::NOT_EQUAL, -15.5]]);
        $this->assert($result, ['product_two']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_number_float_negative" expects a numeric as data, "string" given.');

        $this->executeFilter([['a_number_float_negative', Operators::NOT_EQUAL, 'string']]);
    }

    public function testErrorDataIsNull()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_number_float_negative" expects a numeric as data, "NULL" given.');

        $this->executeFilter([['a_number_float_negative', Operators::NOT_EQUAL, null]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_number_float_negative" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['a_number_float_negative', Operators::BETWEEN, '-15.5']]);
    }
}
