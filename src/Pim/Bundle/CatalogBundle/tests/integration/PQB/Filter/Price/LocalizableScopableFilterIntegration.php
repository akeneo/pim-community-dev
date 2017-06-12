<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Price;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'             => 'a_scopable_localizable_price',
            'type'             => AttributeTypes::PRICE_COLLECTION,
            'localizable'      => true,
            'scopable'         => true,
            'decimals_allowed' => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_scopable_localizable_price' => [
                    [
                        'data'   => [['amount' => '-5.00', 'currency' => 'USD']],
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                    ],
                    ['data' => [['amount' => '14', 'currency' => 'USD']], 'locale' => 'en_US', 'scope' => 'tablet'],
                    [
                        'data'   => [['amount' => '100', 'currency' => 'USD']],
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                    ],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_scopable_localizable_price' => [
                    [
                        'data'   => [['amount' => '-5.00', 'currency' => 'USD']],
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                    ],
                    ['data' => [['amount' => '10', 'currency' => 'USD']], 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => [['amount' => '75', 'currency' => 'USD']], 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    [
                        'data'   => [['amount' => '75', 'currency' => 'USD']],
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                    ],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::LOWER_THAN,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::LOWER_THAN,
                ['amount' => 10.0001, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::LOWER_THAN,
                ['amount' => 80, 'currency' => 'USD'],
                ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 100, 'currency' => 'USD'],
                ['locale' => 'fr_FR', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::EQUALS,
                ['amount' => -5, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::EQUALS,
                ['amount' => -5, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::GREATER_THAN,
                ['amount' => -5, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::GREATER_THAN,
                ['amount' => -5.0001, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => -5, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => 80, 'currency' => 'USD'],
                ['locale' => 'fr_FR', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmptyOnAllCurrencies()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                [],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                [],
                ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['empty_product', 'product_one']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY,
                [],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY,
                [],
                ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['empty_product', 'product_one']);
    }

    public function testOperatorEmptyForCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY_FOR_CURRENCY,
                ['currency' => 'EUR'],
                ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['empty_product', 'product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmptyOnAtLeastOneCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY,
                [],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_NOT_EMPTY,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_NOT_EMPTY,
                [],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotEmptyForCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'tablet'],
            ],
        ]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects a locale, none given.
     */
    public function testErrorPriceLocalizableAndScopable()
    {
        $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 250, 'currency' => 'USD'],
            ],
        ]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects a scope, none given.
     */
    public function testErrorPriceLocalizable()
    {
        $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 250, 'currency' => 'USD'],
                ['locale' => 'fr_FR'],
            ],
        ]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'NOT_FOUND'],
            ],
        ]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->executeFilter([
            [
                'a_scopable_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'en_US', 'scope' => 'NOT_FOUND'],
            ],
        ]);
    }
}
