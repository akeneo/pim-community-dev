<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Price;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_price' => [
                        ['data' => [['amount' => '10.55', 'currency' => 'EUR']], 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => [['amount' => '25', 'currency' => 'USD']], 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_price' => [
                        ['data' => [
                            ['amount' => '2', 'currency' => 'EUR'],
                            ['amount' => '2.2', 'currency' => 'USD']
                        ], 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => [['amount' => '30', 'currency' => 'EUR']], 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_scopable_price', Operators::LOWER_THAN, ['amount' => 10.55, 'currency' => 'EUR'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_price', Operators::LOWER_THAN, ['amount' => 10.5501, 'currency' => 'EUR'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_price', Operators::LOWER_THAN, ['amount' => 35, 'currency' => 'USD'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->execute([['a_scopable_price', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.55, 'currency' => 'EUR'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_price', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.5501, 'currency' => 'EUR'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_scopable_price', Operators::EQUALS, ['amount' => 25, 'currency' => 'EUR'], ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_price', Operators::EQUALS, ['amount' => 25, 'currency' => 'USD'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_scopable_price', Operators::GREATER_THAN, ['amount' => 30, 'currency' => 'EUR'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_price', Operators::GREATER_THAN, ['amount' => 25, 'currency' => 'USD'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_price', Operators::GREATER_THAN, ['amount' => 24.4999, 'currency' => 'USD'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->execute([['a_scopable_price', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 30, 'currency' => 'EUR'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_price', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 25, 'currency' => 'EUR'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_scopable_price', Operators::IS_EMPTY, ['amount' => '', 'currency' => ''], ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([['a_scopable_price', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_scopable_price', Operators::IS_NOT_EMPTY, ['amount' => '', 'currency' => ''], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_price', Operators::IS_NOT_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_price', Operators::IS_NOT_EMPTY, ['amount' => '', 'currency' => 'EUR'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_price', Operators::IS_NOT_EMPTY, ['amount' => '', 'currency' => 'USD'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_price', Operators::IS_NOT_EMPTY, ['amount' => '', 'currency' => 'USD'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_scopable_price', Operators::NOT_EQUAL, ['amount' => 30, 'currency' => 'USD'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_price', Operators::NOT_EQUAL, ['amount' => 30, 'currency' => 'EUR'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_price', Operators::NOT_EQUAL, ['amount' => 3, 'currency' => 'EUR'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_price" expects a scope, none given.
     */
    public function testErrorPriceScopable()
    {
        $this->execute([['a_scopable_price', Operators::NOT_EQUAL, ['amount' => 250, 'currency' => 'EUR']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_price" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_price', Operators::NOT_EQUAL, ['amount' => 10, 'currency' => 'EUR'], ['scope' => 'NOT_FOUND']]]);
    }
}
