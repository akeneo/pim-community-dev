<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Boolean;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
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

        $this->createAttribute([
            'code'                => 'a_scopable_yes_no',
            'type'                => AttributeTypes::BOOLEAN,
            'localizable'         => false,
            'scopable'            => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_scopable_yes_no' => [
                    ['data' => true, 'scope' => 'ecommerce', 'locale' => null],
                    ['data' => false, 'scope' => 'tablet', 'locale' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_scopable_yes_no' => [
                    ['data' => true, 'scope' => 'ecommerce', 'locale' => null],
                    ['data' => true, 'scope' => 'tablet', 'locale' => null],
                ]
            ]
        ]);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_scopable_yes_no', Operators::EQUALS, true, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_scopable_yes_no', Operators::EQUALS, false, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_scopable_yes_no', Operators::EQUALS, true, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_scopable_yes_no', Operators::NOT_EQUAL, true, ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_yes_no', Operators::NOT_EQUAL, true, ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testErrorScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_yes_no" expects a scope, none given.');
        $this->executeFilter([['a_scopable_yes_no', Operators::NOT_EQUAL, true]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_yes_no" expects an existing scope, "NOT_FOUND" given.');
        $this->executeFilter([['a_scopable_yes_no', Operators::NOT_EQUAL, true, ['scope' => 'NOT_FOUND']]]);
    }
}
