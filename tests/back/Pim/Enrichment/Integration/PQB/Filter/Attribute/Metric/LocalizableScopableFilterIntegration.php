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
class LocalizableScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_scopable_localizable_metric',
            'type'                => AttributeTypes::METRIC,
            'localizable'         => true,
            'scopable'            => true,
            'negative_allowed'    => true,
            'decimals_allowed'    => true,
            'metric_family'       => 'Power',
            'default_metric_unit' => 'KILOWATT'
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_scopable_localizable_metric']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_scopable_localizable_metric' => [
                    ['data' => ['amount' => '-5.00', 'unit' => 'KILOWATT'], 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => ['amount' => '14', 'unit' => 'KILOWATT'], 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => ['amount' => '100', 'unit' => 'KILOWATT'], 'locale' => 'fr_FR', 'scope' => 'tablet'],
                ],
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_scopable_localizable_metric' => [
                    ['data' => ['amount' => '-5.00', 'unit' => 'KILOWATT'], 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => ['amount' => '10', 'unit' => 'KILOWATT'], 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => ['amount' => '75', 'unit' => 'KILOWATT'], 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ['data' => ['amount' => '75', 'unit' => 'KILOWATT'], 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::LOWER_THAN,
            ['amount' => 10, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::LOWER_THAN,
            ['amount' => 10.0001, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::LOWER_THAN,
            ['amount' => 80, 'unit' => 'KILOWATT'],
            ['locale' => 'fr_FR', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 10, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::LOWER_OR_EQUAL_THAN,
            ['amount' => 100, 'unit' => 'KILOWATT'],
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::EQUALS,
            ['amount' => -5, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::EQUALS,
            ['amount' => -5, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::GREATER_THAN,
            ['amount' => -5, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::GREATER_THAN,
            ['amount' => -5.0001, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => -5, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::GREATER_OR_EQUAL_THAN,
            ['amount' => 80, 'unit' => 'KILOWATT'],
            ['locale' => 'fr_FR', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_scopable_localizable_metric', Operators::IS_EMPTY, [], ['locale' => 'en_US', 'scope' => 'tablet']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_scopable_localizable_metric', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US', 'scope' => 'tablet']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::NOT_EQUAL,
            ['amount' => 10, 'unit' => 'WATT'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([[
            'a_scopable_localizable_metric',
            Operators::NOT_EQUAL,
            ['amount' => 10, 'unit' => 'KILOWATT'],
            ['locale' => 'en_US', 'scope' => 'tablet']
        ]]);
        $this->assert($result, ['product_one']);
    }

    public function testErrorMetricLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_localizable_metric" expects a locale, none given.');

        $this->executeFilter([['a_scopable_localizable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT']]]);
    }

    public function testErrorMetricScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_localizable_metric" expects a scope, none given.');

        $this->executeFilter([['a_scopable_localizable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT'], ['locale' => 'fr_FR']]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_localizable_metric" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_scopable_localizable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT'], ['locale' => 'NOT_FOUND']]]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_localizable_metric" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_scopable_localizable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT'], ['locale' => 'en_US', 'scope' => 'NOT_FOUND']]]);
    }
}
