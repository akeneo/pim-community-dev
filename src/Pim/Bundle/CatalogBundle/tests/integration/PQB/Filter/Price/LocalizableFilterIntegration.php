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
class LocalizableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code' => 'a_localizable_price',
            'type' => AttributeTypes::PRICE_COLLECTION,
            'localizable' => true,
            'scopable' => false,
            'decimals_allowed' => false,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_price' => [
                    ['data' => [['amount' => 20, 'currency' => 'EUR']], 'locale' => 'en_US', 'scope' => null],
                    ['data' => [['amount' => 21, 'currency' => 'EUR']], 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_price' => [
                    ['data' => [['amount' => 10, 'currency' => 'EUR']], 'locale' => 'en_US', 'scope' => null],
                    ['data' => [['amount' => 1, 'currency' => 'EUR']], 'locale' => 'fr_FR', 'scope' => null],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::LOWER_THAN,
                ['amount' => 1, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::LOWER_THAN,
                ['amount' => 20, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::LOWER_THAN,
                ['amount' => 21.0001, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 1, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 20, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::LOWER_OR_EQUAL_THAN,
                ['amount' => 21, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::EQUALS,
                ['amount' => 21, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::EQUALS,
                ['amount' => 21, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::GREATER_THAN,
                ['amount' => 20, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::GREATER_THAN,
                ['amount' => 21, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::GREATER_THAN,
                ['amount' => 9, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => 25, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => 20, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::GREATER_OR_EQUAL_THAN,
                ['amount' => 1, 'currency' => 'EUR'],
                ['locale' => 'fr_FR'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmptyOnAllCurrencies()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_EMPTY,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_localizable_price', Operators::IS_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_EMPTY_ON_ALL_CURRENCIES,
                [],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorEmptyForCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_EMPTY_FOR_CURRENCY,
                ['currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['empty_product', 'product_one', 'product_two']);
    }

    public function testOperatorNotEmptyOnAtLeastOneCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY,
                [],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_NOT_EMPTY,
                ['amount' => '', 'currency' => ''],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotEmptyForCurrency()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'USD'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::IS_NOT_EMPTY_FOR_CURRENCY,
                ['currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([
            [
                'a_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 20, 'currency' => 'EUR'],
                ['locale' => 'en_US'],
            ],
        ]);
        $this->assert($result, ['product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_price" expects a locale, none given.
     */
    public function testErrorPriceLocalizable()
    {
        $this->executeFilter([['a_localizable_price', Operators::NOT_EQUAL, ['amount' => 250, 'currency' => 'USD']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_price" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([
            [
                'a_localizable_price',
                Operators::NOT_EQUAL,
                ['amount' => 10, 'currency' => 'USD'],
                ['locale' => 'NOT_FOUND'],
            ],
        ]);
    }
}
