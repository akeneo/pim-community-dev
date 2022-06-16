<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Options;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
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

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_scopable_multi_select']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_scopable_multi_select', 'ecommerce', null, ['orange']),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_scopable_multi_select', 'ecommerce', null, ['black', 'purple']),
            new SetMultiSelectValue('a_scopable_multi_select', 'tablet', null, ['black', 'purple']),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_scopable_multi_select', Operators::IN_LIST, ['orange'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_multi_select', Operators::IN_LIST, ['orange'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_scopable_multi_select', Operators::IN_LIST, ['orange', 'black'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_scopable_multi_select', Operators::IS_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_scopable_multi_select', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_scopable_multi_select', Operators::IS_NOT_EMPTY, [], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_scopable_multi_select', Operators::NOT_IN_LIST, ['black'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product', 'product_one']);
    }

    public function testErrorOptionScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_multi_select" expects a scope, none given.');

        $this->executeFilter([['a_scopable_multi_select', Operators::IN_LIST, ['orange']]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_multi_select" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_scopable_multi_select', Operators::IN_LIST, ['orange'], ['scope' => 'NOT_FOUND']]]);
    }
}
