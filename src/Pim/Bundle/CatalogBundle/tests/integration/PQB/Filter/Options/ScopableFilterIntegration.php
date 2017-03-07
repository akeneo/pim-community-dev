<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Options;

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
                'code'                => 'a_scopable_multi_select',
                'type'                => AttributeTypes::OPTION_MULTI_SELECT,
                'localizable'         => false,
                'scopable'            => true
            ]);

            $this->createAttributeOption([
                'attribute' => 'a_scopable_multi_select',
                'code'      => 'orange'
            ]);

            $this->createAttributeOption([
                'attribute' => 'a_scopable_multi_select',
                'code'      => 'black'
            ]);

            $this->createAttributeOption([
                'attribute' => 'a_scopable_multi_select',
                'code'      => 'purple'
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_multi_select' => [
                        ['data' => ['orange'], 'locale' => null, 'scope' => 'ecommerce']
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_multi_select' => [
                        ['data' => ['black', 'purple'], 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => ['black', 'purple'], 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorIn()
    {
        $result = $this->execute([['a_scopable_multi_select', Operators::IN_LIST, ['orange'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_multi_select', Operators::IN_LIST, ['orange'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_scopable_multi_select', Operators::IN_LIST, ['orange', 'black'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_scopable_multi_select', Operators::IS_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->execute([['a_scopable_multi_select', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_scopable_multi_select', Operators::IS_NOT_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->execute([['a_scopable_multi_select', Operators::NOT_IN_LIST, ['black'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_multi_select" expects a scope, none given.
     */
    public function testErrorOptionScopable()
    {
        $this->execute([['a_scopable_multi_select', Operators::IN_LIST, ['orange']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_multi_select" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_multi_select', Operators::IN_LIST, ['orange'], ['scope' => 'NOT_FOUND']]]);
    }
}
