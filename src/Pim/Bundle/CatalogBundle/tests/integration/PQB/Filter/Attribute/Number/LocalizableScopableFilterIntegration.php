<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Number;

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
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_number',
            'type'                => AttributeTypes::NUMBER,
            'localizable'         => true,
            'scopable'            => true,
            'negative_allowed'    => true,
            'decimals_allowed'    => true,
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_scopable_number' => [
                    ['data' => -15, 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => -15, 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => -14, 'locale' => 'fr_FR', 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_scopable_number' => [
                    ['data' => 19, 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 19, 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 19, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 19, 'locale' => 'fr_FR', 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::LOWER_THAN, -14, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::LOWER_THAN, -14, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::LOWER_OR_EQUAL_THAN, -15, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::LOWER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::EQUALS, 15, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::EQUALS, -15, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::GREATER_THAN, -15, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::GREATER_THAN, -14.5001, ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::GREATER_OR_EQUAL_THAN, -15, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::GREATER_OR_EQUAL_THAN, -14.5001, ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::IS_EMPTY, 0, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::IS_EMPTY, 0, ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::IS_NOT_EMPTY, 0, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::NOT_EQUAL, 15, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_number', Operators::NOT_EQUAL, -15, ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_number" expects a locale, none given.
     */
    public function testErrorLocalizable()
    {
        $this->executeFilter([['a_localizable_scopable_number', Operators::NOT_EQUAL, 12]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_number" expects a scope, none given.
     */
    public function testErrorScopable()
    {
        $this->executeFilter([['a_localizable_scopable_number', Operators::NOT_EQUAL, 12, ['locale' => 'fr_FR']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_number" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([['a_localizable_scopable_number', Operators::NOT_EQUAL, 12, ['locale' => 'NOT_FOUND']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_number" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->executeFilter([['a_localizable_scopable_number', Operators::NOT_EQUAL, 12, ['locale' => 'fr_FR', 'scope' => 'NOT_FOUND']]]);
    }
}
