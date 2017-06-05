<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Option;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

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
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_select_scopable_simple_select',
            'type'                => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable'         => false,
            'scopable'            => true
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_select_scopable_simple_select',
            'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_select_scopable_simple_select',
            'code'      => 'black'
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_select_scopable_simple_select' => [
                    ['data' => 'orange', 'locale' => null, 'scope' => 'ecommerce']
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_select_scopable_simple_select' => [
                    ['data' => 'black', 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => 'black', 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_select_scopable_simple_select', Operators::IN_LIST, ['orange'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_select_scopable_simple_select', Operators::IN_LIST, ['orange', 'black'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_select_scopable_simple_select', Operators::IS_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_select_scopable_simple_select', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_select_scopable_simple_select', Operators::IS_NOT_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_select_scopable_simple_select', Operators::NOT_IN_LIST, ['black'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_select_scopable_simple_select" expects a scope, none given.
     */
    public function testErrorOptionScopable()
    {
        $this->executeFilter([['a_select_scopable_simple_select', Operators::IN_LIST, ['orange']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_select_scopable_simple_select" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->executeFilter([['a_select_scopable_simple_select', Operators::IN_LIST, ['orange'], ['scope' => 'NOT_FOUND']]]);
    }
}
