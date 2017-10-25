<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Price;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_one', [
            'values' => [
                'a_price' => [
                    ['data' => [
                        ['amount' => '10.55', 'currency' => 'EUR'],
                        ['amount' => '11', 'currency' => 'USD']
                    ], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_price' => [
                    ['data' => [['amount' => '15', 'currency' => 'EUR']], 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_price', Operators::LOWER_THAN, ['amount' => 10.55, 'currency' => 'EUR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_price', Operators::LOWER_THAN, ['amount' => 11, 'currency' => 'USD']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_price', Operators::LOWER_THAN, ['amount' => 10.5501, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_price', Operators::LOWER_THAN, ['amount' => 16, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_price', Operators::LOWER_THAN, ['amount' => 16, 'currency' => 'USD']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([['a_price', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.4999, 'currency' => 'EUR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_price', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.55, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_price', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 11, 'currency' => 'USD']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_price', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 15, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_price', Operators::EQUALS, ['amount' => 10.5501, 'currency' => 'EUR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_price', Operators::EQUALS, ['amount' => 10.55, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_price', Operators::EQUALS, ['amount' => 11, 'currency' => 'USD']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_price', Operators::GREATER_THAN, ['amount' => 15, 'currency' => 'EUR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_price', Operators::GREATER_THAN, ['amount' => 10.4999, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_price', Operators::GREATER_THAN, ['amount' => 10.9999, 'currency' => 'USD']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([['a_price', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15.01, 'currency' => 'EUR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_price', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 10.55, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_price', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_price', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 11, 'currency' => 'USD']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmptyOnAllCurrencies()
    {
        $result = $this->executeFilter([['a_price', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_price', Operators::IS_EMPTY_ON_ALL_CURRENCIES, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorEmptyForCurrency()
    {
        $result = $this->executeFilter([['a_price', Operators::IS_EMPTY_FOR_CURRENCY, ['currency' => 'USD']]]);
        $this->assert($result, ['empty_product', 'product_two']);

        $result = $this->executeFilter([['a_price', Operators::IS_EMPTY_FOR_CURRENCY, ['currency' => 'EUR']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmptyOnAtLeastOneCurrency(){
        $result = $this->executeFilter([['a_price', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_price', Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotEmptyForCurrency()
    {
        $result = $this->executeFilter([['a_price', Operators::IS_NOT_EMPTY_FOR_CURRENCY, ['currency' => 'USD']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_price', Operators::IS_NOT_EMPTY_FOR_CURRENCY, ['currency' => 'EUR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_price', Operators::NOT_EQUAL, ['amount' => 15, 'currency' => 'EUR']]]);
        $this->assert($result, ['product_one']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_price" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['a_price', Operators::NOT_EQUAL, 'string']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_price" expects an array with the key "amount".
     */
    public function testErrorAmountIsMissing()
    {
        $this->executeFilter([['a_price', Operators::NOT_EQUAL, ['currency' => 'USD']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_price" expects an array with the key "currency".
     */
    public function testErrorCurrencyIsMissing()
    {
        $this->executeFilter([['a_price', Operators::NOT_EQUAL, ['amount' => 2]]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "a_price" expects a valid currency. The currency does not exist, "NOT_FOUND" given.
     */
    public function testErrorCurrencyNotFound()
    {
        $this->executeFilter([['a_price', Operators::NOT_EQUAL, ['amount' => 10, 'currency' => 'NOT_FOUND']]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_price" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['a_price', Operators::BETWEEN, ['amount' => 15, 'currency' => 'EUR']]]);
    }
}
