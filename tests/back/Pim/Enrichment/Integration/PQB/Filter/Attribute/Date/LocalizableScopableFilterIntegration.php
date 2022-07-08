<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
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

        $this->activateLocaleForChannel('fr_Fr', 'ecommerce');

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
            new SetFamily('a_family'),
            new SetDateValue('a_localizable_scopable_date', 'ecommerce', 'en_US', new \DateTime('2016-04-23')),
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'en_US', new \DateTime('2016-04-23')),
            new SetDateValue('a_localizable_scopable_date', 'ecommerce', 'fr_FR', new \DateTime('2016-05-23')),
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'fr_FR', new \DateTime('2016-05-23')),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetDateValue('a_localizable_scopable_date', 'ecommerce', 'en_US', new \DateTime('2016-09-23')),
            new SetDateValue('a_localizable_scopable_date', 'tablet', 'en_US', new \DateTime('2016-09-23')),
            new SetDateValue('a_localizable_scopable_date', 'ecommerce', 'fr_FR', new \DateTime('2016-09-23')),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
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

    public function testErrorMetricLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_date" expects a locale, none given.');
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23']]);
    }

    public function testErrorMetricScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_date" expects a scope, none given.');
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'en_US']]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_date" expects an existing and activated locale, "NOT_FOUND" given.');
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'NOT_FOUND']]]);
    }

    public function testNotFoundScope()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_date" expects an existing scope, "NOT_FOUND" given.');
        $this->executeFilter([['a_localizable_scopable_date', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'en_US', 'scope' => 'NOT_FOUND']]]);
    }
}
