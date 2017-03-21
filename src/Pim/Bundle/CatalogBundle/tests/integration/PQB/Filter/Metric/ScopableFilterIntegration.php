<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Metric;

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
                'code'                => 'a_scopable_metric',
                'type'                => AttributeTypes::METRIC,
                'localizable'         => false,
                'scopable'            => true,
                'decimals_allowed'    => true,
                'metric_family'       => 'Length',
                'default_metric_unit' => 'METER'
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_scopable_metric' => [
                        ['data' => ['amount' => '10.55', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => ['amount' => '25', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_scopable_metric' => [
                        ['data' => ['amount' => '2', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'ecommerce'],
                        ['data' => ['amount' => '30', 'unit' => 'CENTIMETER'], 'locale' => null, 'scope' => 'tablet']
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_scopable_metric', Operators::LOWER_THAN, ['amount' => 10.55, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_metric', Operators::LOWER_THAN, ['amount' => 10.5501, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_metric', Operators::LOWER_THAN, ['amount' => 10.55, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->execute([['a_scopable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 2, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 2, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.55, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_scopable_metric', Operators::EQUALS, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_metric', Operators::EQUALS, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_scopable_metric', Operators::GREATER_THAN, ['amount' => 30, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_scopable_metric', Operators::GREATER_THAN, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->execute([['a_scopable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 30, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_scopable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 25, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_scopable_metric', Operators::IS_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_scopable_metric', Operators::IS_NOT_EMPTY, [], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 30, 'unit' => 'METER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 30, 'unit' => 'CENTIMETER'], ['scope' => 'tablet']]]);
        $this->assert($result, ['product_one']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_metric" expects a scope, none given.
     */
    public function testErrorMetricScopable()
    {
        $this->execute([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_scopable_metric" expects an existing scope, "NOT_FOUND" given.
     */
    public function testScopeNotFound()
    {
        $this->execute([['a_scopable_metric', Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'KILOWATT'], ['scope' => 'NOT_FOUND']]]);
    }
}
