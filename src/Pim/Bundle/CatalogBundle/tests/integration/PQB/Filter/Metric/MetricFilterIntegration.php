<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Metric;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('product_one', [
                'values' => [
                    'a_metric' => [
                        ['data' => ['amount' => '10.55', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_metric' => [
                        ['data' => ['amount' => '15', 'unit' => 'KILOWATT'], 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_metric', Operators::LOWER_THAN, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_metric', Operators::LOWER_THAN, ['amount' => 10.5501, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_metric', Operators::LOWER_THAN, ['amount' => 16, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorInferiorOrEquals()
    {
        $result = $this->execute([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.4999, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_metric', Operators::LOWER_OR_EQUAL_THAN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_metric', Operators::EQUALS, ['amount' => 10.5501, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_metric', Operators::EQUALS, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_metric', Operators::GREATER_THAN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_metric', Operators::GREATER_THAN, ['amount' => 10.4999, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorSuperiorOrEquals()
    {
        $result = $this->execute([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15.01, 'unit' => 'KILOWATT']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 10.55, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_metric', Operators::GREATER_OR_EQUAL_THAN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_metric', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_metric', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_metric', Operators::NOT_EQUAL, ['amount' => 15, 'unit' => 'KILOWATT']]]);
        $this->assert($result, ['product_one']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_metric" expects an array as data, "string" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['a_metric', Operators::NOT_EQUAL, 'string']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_metric" expects an array with the key "amount" as data.
     */
    public function testErrorAmountIsMissing()
    {
        $this->execute([['a_metric', Operators::NOT_EQUAL, ['unit' => 'WATT']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_metric" expects an array with the key "unit" as data.
     */
    public function testErrorCurrencyIsMissing()
    {
        $this->execute([['a_metric', Operators::NOT_EQUAL, ['amount' => '']]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage The unit does not exist in the attribute's family "Power"
     */
    public function testErrorUnitNotFound()
    {
        $this->execute([['a_metric', Operators::NOT_EQUAL, ['amount' => 10, 'unit' => 'NOT_FOUND']]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_metric" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['a_metric', Operators::BETWEEN, ['amount' => 15, 'unit' => 'KILOWATT']]]);
    }
}
