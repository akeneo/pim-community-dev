<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Metric;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

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
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_metric',
            'type'                => AttributeTypes::METRIC,
            'localizable'         => true,
            'decimals_allowed'    => false,
            'metric_family'       => 'Length',
            'default_metric_unit' => 'METER',
        ]);

        $this->createProduct('product_one', [
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => 20, 'unit' => 'METER'], 'locale' => 'en_US', 'scope' => null],
                    ['data' => ['amount' => 21, 'unit' => 'METER'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'values' => [
                'a_localizable_metric' => [
                    ['data' => ['amount' => 10, 'unit' => 'METER'], 'locale' => 'en_US', 'scope' => null],
                    ['data' => ['amount' => 1, 'unit' => 'METER'], 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
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

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_metric" expects a locale, none given.
     */
    public function testErrorMetricLocalizable()
    {
        $this->executeFilter([['a_localizable_metric', Operators::NOT_EQUAL, ['amount' => 250, 'unit' => 'KILOWATT']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_metric" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->executeFilter([['a_localizable_metric', Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'KILOWATT'], ['locale' => 'NOT_FOUND']]]);
    }
}
