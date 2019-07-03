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
class LocalizableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_metric',
            'type'                => AttributeTypes::METRIC,
            'localizable'         => true,
            'negative_allowed'    => true,
            'decimals_allowed'    => false,
            'metric_family'       => 'Length',
            'default_metric_unit' => 'METER',
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_metric']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => 20, 'unit' => 'METER'], 'locale' => 'en_US', 'scope' => null],
                    ['data' => ['amount' => 21, 'unit' => 'METER'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => 10, 'unit' => 'METER'], 'locale' => 'en_US', 'scope' => null],
                    ['data' => ['amount' => 1, 'unit' => 'METER'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::LOWER_THAN, ['amount' => 1, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_metric', Operators::LOWER_THAN, ['amount' => 20, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_metric', Operators::LOWER_THAN, ['amount' => 21.0001, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 1, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 20, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 21, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::EQUALS, ['amount' => 21, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_metric', Operators::EQUALS, ['amount' => 21, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::GREATER_THAN, ['amount' => 20, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_metric', Operators::GREATER_THAN, ['amount' => 21, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_metric', Operators::GREATER_THAN, ['amount' => 9, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 25, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 20, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 1, 'unit' => 'METER'], ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::IS_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_metric', Operators::NOT_EQUAL, ['amount' => 20, 'unit' => 'METER'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);
    }

    public function testErrorMetricLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_metric" expects a locale, none given.');

        $this->executeFilter([['a_localizable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT']]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_metric" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_metric', Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'KILOWATT'], ['locale' => 'NOT_FOUND']]]);
    }
}
