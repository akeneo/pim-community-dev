<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Metric;

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
            'code'                => 'a_scopable_metric',
            'type'                => AttributeTypes::METRIC,
            'localizable'         => false,
            'scopable'            => true,
            'decimals_allowed'    => true,
            'negative_allowed'    => true,
            'metric_family'       => 'Length',
            'default_metric_unit' => 'METER'
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_scopable_metric']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_scopable_metric' => [
                    ['data' => ['amount' => '10.55', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => ['amount' => '25', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_scopable_metric' => [
                    ['data' => ['amount' => '2', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'ecommerce'],
                    ['data' => ['amount' => '30', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::LOWER_THAN, ['amount' => 10.55, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_metric', Operators::LOWER_THAN, ['amount' => 10.5501, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_scopable_metric', Operators::LOWER_THAN, ['amount' => 10.55, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 2, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 2, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_scopable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.55, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::EQUALS, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_metric', Operators::EQUALS, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::GREATER_THAN, ['amount' => 30, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_metric', Operators::GREATER_THAN, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 30, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_scopable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::IS_NOT_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 30, 'unit' => 'METER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 30, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testErrorMetricScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_metric" expects a scope, none given.');

        $this->executeFilter([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT']]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_metric" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'KILOWATT'], ['scope' => 'NOT_FOUND']]]);
    }
}
