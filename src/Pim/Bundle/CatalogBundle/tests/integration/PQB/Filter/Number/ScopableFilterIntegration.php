<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Number;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
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
            $this->createAttribute([
                'code'                => 'a_scopable_number',
                'type'                => AttributeTypes::NUMBER,
                'localizable'         => false,
                'scopable'            => true,
                'negative_allowed'    => true
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_number' => [
                        ['data' => -15, 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => -14, 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_number' => [
                        ['data' => 19, 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_scopable_number', Operators::LOWER_THAN, -14, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_number', Operators::LOWER_THAN, -14, ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->execute([['a_scopable_number', Operators::LOWER_OR_EQUAL_THAN, -15, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_number', Operators::LOWER_OR_EQUAL_THAN, -14, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_scopable_number', Operators::EQUALS, -15, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_scopable_number', Operators::GREATER_THAN, -14.0001, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_number', Operators::GREATER_THAN, -14, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->execute([['a_scopable_number', Operators::GREATER_OR_EQUAL_THAN, -14, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_number', Operators::GREATER_OR_EQUAL_THAN, -14, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_scopable_number', Operators::IS_EMPTY, 0, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_scopable_number', Operators::IS_NOT_EMPTY, 0, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_scopable_number', Operators::NOT_EQUAL, 15, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_number', Operators::NOT_EQUAL, -15, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_number" expects a scope, none given.
     */
    public function testErrorScopable()
    {
        $this->execute([['a_scopable_number', Operators::NOT_EQUAL, 12]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_number" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_number', Operators::NOT_EQUAL, 12, ['scope' => 'NOT_FOUND']]]);
    }
}
