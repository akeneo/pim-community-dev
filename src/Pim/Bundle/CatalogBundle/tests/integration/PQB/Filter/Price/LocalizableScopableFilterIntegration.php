<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Price;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createAttribute([
                'code'                => 'a_scopable_localizable_price',
                'type'                => AttributeTypes::PRICE_COLLECTION,
                'localizable'         => true,
                'scopable'            => true,
                'decimals_allowed'    => true
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_localizable_price' => [
                        ['data' => [['amount' => '-5.00', 'currency' => 'USD']], 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => [['amount' => '14', 'currency' => 'USD']], 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => [['amount' => '100', 'currency' => 'USD']], 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_localizable_price' => [
                        ['data' => [['amount' => '-5.00', 'currency' => 'USD']], 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => [['amount' => '10', 'currency' => 'USD']], 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => [['amount' => '75', 'currency' => 'USD']], 'locale' => 'fr_FR', 'scope' => 'tablet'],
                        ['data' => [['amount' => '75', 'currency' => 'USD']], 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ],
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::LOWER_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::LOWER_THAN,
            ['amount' => 10.0001, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::LOWER_THAN,
            ['amount' => 80, 'currency' => 'USD'],
            ['locale' => 'fr_FR', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 10, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 100, 'currency' => 'USD'],
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::EQUALS,
            ['amount' => -5, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::EQUALS,
            ['amount' => -5, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::GREATER_THAN,
            ['amount' => -5, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, []);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::GREATER_THAN,
            ['amount' => -5.0001, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => -5, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 80, 'currency' => 'USD'],
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::IS_EMPTY,
            ['amount' => '', 'currency' => ''],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::IS_EMPTY,
            [],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::IS_NOT_EMPTY,
            ['amount' => '', 'currency' => ''],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::IS_NOT_EMPTY,
            [],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::IS_NOT_EMPTY,
            ['amount' => '', 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::IS_NOT_EMPTY,
            ['amount' => '', 'currency' => 'EUR'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::NOT_EQUAL,
            ['amount' => 10, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([[
            'a_scopable_localizable_price',
            Operators::NOT_EQUAL,
            ['amount' => 10, 'currency' => 'USD'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects a locale, none given.
     */
    public function testErrorPriceLocalizableAndScopable()
    {
        $this->execute([['a_scopable_localizable_price', Operators::NOT_EQUAL, ['amount' => 250, 'currency' => 'USD']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects a scope, none given.
     */
    public function testErrorPriceLocalizable()
    {
        $this->execute([['a_scopable_localizable_price', Operators::NOT_EQUAL, ['amount' => 250, 'currency' => 'USD'], ['locale' => 'fr_FR']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->execute([['a_scopable_localizable_price', Operators::NOT_EQUAL, ['amount' => 10, 'currency' => 'USD'], ['locale' => 'NOT_FOUND']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_localizable_price" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_localizable_price', Operators::NOT_EQUAL, ['amount' => 10, 'currency' => 'USD'], ['locale' => 'en_US', 'scope' => 'NOT_FOUND']]]);
    }
}
