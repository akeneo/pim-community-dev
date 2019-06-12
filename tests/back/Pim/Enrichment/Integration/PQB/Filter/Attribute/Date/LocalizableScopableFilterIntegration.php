<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
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
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_date',
            'type'                => AttributeTypes::DATE,
            'localizable'         => true,
            'scopable'            => true
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_scopable_date']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_scopable_date' => [
                    ['data' => '2016-04-23', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => '2016-04-23', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => '2016-05-23', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => '2016-05-23', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_scopable_date' => [
                    ['data' => '2016-09-23', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => '2016-09-23', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => '2016-09-23', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::LOWER_THAN, '2016-04-24', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::LOWER_THAN, '2016-04-24', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::LOWER_THAN, '2016-09-24', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::EQUALS, '2016-09-23', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::EQUALS, '2016-05-23', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::EQUALS, '2016-05-23', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::GREATER_THAN, '2016-09-23', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::GREATER_THAN, '2016-09-23', ['locale' => 'fr_FR', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::GREATER_THAN, '2016-09-22', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::IS_EMPTY, [], ['locale' => 'en_US', 'scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::IS_EMPTY, [], ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assert($result, ['product_two', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorBetween()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::BETWEEN, ['2016-09-23', '2016-09-23'], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::BETWEEN, ['2016-04-23', '2016-09-23'], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_BETWEEN, ['2016-09-23', '2016-09-23'], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_BETWEEN, [
            new \DateTime('2016-04-23T00:00:00'), '2016-09-23'
        ], ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, []);
    }

    /**
     * @expectedException \Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_date" expects a locale, none given.
     */
    public function testErrorMetricLocalizable()
    {
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23']]);
    }

    /**
     * @expectedException \Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_date" expects a scope, none given.
     */
    public function testErrorMetricScopable()
    {
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'en_US']]]);
    }

    /**
     * @expectedException \Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_date" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'NOT_FOUND']]]);
    }

    /**
     * @expectedException \Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_date" expects an existing scope, "NOT_FOUND" given.
     */
    public function testNotFoundScope()
    {
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'en_US', 'scope' => 'NOT_FOUND']]]);
    }
}
