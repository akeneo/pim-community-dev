<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Option;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_simple_select',
            'type'                => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable'         => true,
            'scopable'            => true
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_scopable_simple_select',
            'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_scopable_simple_select',
            'code'      => 'black'
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_scopable_simple_select']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_scopable_simple_select' => [
                    ['data' => 'orange', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'orange', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'black', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'black', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_scopable_simple_select' => [
                    ['data' => 'black', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'black', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'black', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'black', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange'], ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange'], ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange', 'black'], ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IS_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_localizable_scopable_simple_select', Operators::NOT_IN_LIST, ['black'], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product', 'product_one']);
    }

    public function testErrorOptionLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_simple_select" expects a locale, none given.');

        $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange']]]);
    }

    public function testErrorOptionScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_simple_select" expects a scope, none given.');

        $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange'], ['locale' => 'fr_FR']]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_simple_select" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange'], ['locale' => 'NOT_FOUND']]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_simple_select" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_scopable_simple_select', Operators::IN_LIST, ['orange'], ['locale' => 'en_US', 'scope' => 'NOT_FOUND']]]);
    }
}
