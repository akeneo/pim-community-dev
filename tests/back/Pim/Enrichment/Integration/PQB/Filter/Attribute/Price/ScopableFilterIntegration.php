<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Price;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_scopable_price']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_scopable_price' => [
                    [
                        'data'   => [['amount' => '10.55', 'currency' => 'EUR']],
                        'locale' => null,
                        'scope'  => 'ecommerce',
                    ],
                    ['data' => [['amount' => '25', 'currency' => 'USD']], 'locale' => null, 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_scopable_price' => [
                    [
                        'data'   => [
                            ['amount' => '2', 'currency' => 'EUR'],
                            ['amount' => '2.2', 'currency' => 'USD'],
                        ],
                        'locale' => null,
                        'scope'  => 'ecommerce',
                    ],
                    ['data' => [['amount' => '30', 'currency' => 'EUR']], 'locale' => null, 'scope' => 'tablet'],
                ],
            ],
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::LOWER_THAN,
                ['amount' => 10.55, 'currency' => 'EUR'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::LOWER_THAN,
                ['amount' => 10.5501, 'currency' => 'EUR'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::LOWER_THAN,
                ['amount' => 35, 'currency' => 'USD'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 10.55, 'currency' => 'EUR'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 10.5501, 'currency' => 'EUR'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::EQUALS,
                ['amount' => 25, 'currency' => 'EUR'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::EQUALS,
                ['amount' => 25, 'currency' => 'USD'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::GREATER_THAN,
                ['amount' => 30, 'currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::GREATER_THAN,
                ['amount' => 25, 'currency' => 'USD'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::GREATER_THAN,
                ['amount' => 24.4999, 'currency' => 'USD'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => 30, 'currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => 25, 'currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmptyOnAllCurrencies()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                ['amount' => '', 'currency' => ''],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                [],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_EMPTY,
                ['amount' => '', 'currency' => ''],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_scopable_price', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorEmptyForCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_EMPTY_FOR_CURRENCY,
                ['currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product', 'product_one']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product', 'product_two']);
    }

    public function testOperatorNotEmptyOnAtLeastOneCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY,
                [],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_NOT_EMPTY,
                ['amount' => '', 'currency' => ''],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotEmptyForCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::NOT_EQUAL,
                ['amount' => 30, 'currency' => 'USD'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::NOT_EQUAL,
                ['amount' => 30, 'currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_price',
                Operators::NOT_EQUAL,
                ['amount' => 3, 'currency' => 'EUR'],
                ['scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    public function testErrorPriceScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_price" expects a scope, none given.');

        $this->executeFilter([['a_scopable_price', Operators::NOT_EQUAL, ['amount' => 250, 'currency' => 'EUR']]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_price" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([
            [
                'a_scopable_price',
                Operators::NOT_EQUAL,
                ['amount' => 10, 'currency' => 'EUR'],
                ['scope' => 'NOT_FOUND'],
            ],
        ]);
    }
}
