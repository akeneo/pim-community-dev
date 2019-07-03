<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Date;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_date']
        ]);

        $this->createProduct('product_one', [
            'family' => 'a_family',
            'values' => [
                'a_date' => [
                    ['data' => '2017-02-06', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('product_two', [
            'family' => 'a_family',
            'values' => [
                'a_date' => [
                    ['data' => '2017-02-27', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, '2017-02-06']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, '2017-02-07']]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, '2017-02-28']]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_date', Operators::LOWER_THAN, new \DateTime('2017-02-28T00:00:00')]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_date', Operators::EQUALS, '2017-02-01']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::EQUALS, '2017-02-06']]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_date', Operators::GREATER_THAN, '2017-03-05']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::GREATER_THAN, '2017-02-05']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_date', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_date', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_date', Operators::NOT_EQUAL, '2017-02-20']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorBetween()
    {
        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2017-02-03', '2017-02-06']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2017-02-03', '2017-02-05']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2017-02-06', '2017-02-27']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_date', Operators::BETWEEN, ['2016-02-06', '2017-02-05']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2017-02-03', '2017-02-06']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2017-02-03', '2017-02-05']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2017-02-06', '2017-02-27']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_date', Operators::NOT_BETWEEN, ['2016-02-06', '2017-02-05']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "a_date" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd".');
        $this->executeFilter([['a_date', Operators::BETWEEN, []]]);
    }

    public function testErrorDataIsMalformedWithISODate()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "a_date" expects a string with the format "yyyy-mm-dd" as data, "2016-12-12T00:00:00" given.');
        $this->executeFilter([['a_date', Operators::EQUALS, '2016-12-12T00:00:00']]);
    }
    
    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "a_date" is not supported or does not support operator "CONTAINS"');
        $this->executeFilter([['a_date', Operators::CONTAINS, '2017-02-07']]);
    }
}
