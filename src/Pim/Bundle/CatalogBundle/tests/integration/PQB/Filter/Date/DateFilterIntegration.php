<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Date;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilterIntegration extends AbstractFilterTestCase
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
                    'a_date' => [
                        ['data' => '2017-02-06', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_date' => [
                        ['data' => '2017-02-27', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorInferior()
    {
        $result = $this->execute([['a_date', Operators::LOWER_THAN, '2017-02-06']]);
        $this->assert($result, []);

        $result = $this->execute([['a_date', Operators::LOWER_THAN, '2017-02-07']]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_date', Operators::LOWER_THAN, '2017-02-28']]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_date', Operators::LOWER_THAN, new \DateTime('2017-02-28T00:00:00')]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_date', Operators::EQUALS, '2017-02-01']]);
        $this->assert($result, []);

        $result = $this->execute([['a_date', Operators::EQUALS, '2017-02-06']]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['a_date', Operators::GREATER_THAN, '2017-03-05']]);
        $this->assert($result, []);

        $result = $this->execute([['a_date', Operators::GREATER_THAN, '2017-02-05']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_date', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_date', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_date', Operators::NOT_EQUAL, '2017-02-20']]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorBetween()
    {
        $result = $this->execute([['a_date', Operators::BETWEEN, ['2017-02-03', '2017-02-06']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_date', Operators::BETWEEN, ['2017-02-03', '2017-02-05']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_date', Operators::BETWEEN, ['2017-02-06', '2017-02-27']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_date', Operators::BETWEEN, ['2016-02-06', '2017-02-05']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->execute([['a_date', Operators::NOT_BETWEEN, ['2017-02-03', '2017-02-06']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_date', Operators::NOT_BETWEEN, ['2017-02-03', '2017-02-05']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_date', Operators::NOT_BETWEEN, ['2017-02-06', '2017-02-27']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_date', Operators::NOT_BETWEEN, ['2016-02-06', '2017-02-05']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_date" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd".
     */
    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->execute([['a_date', Operators::BETWEEN, []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "a_date" expects a string with the format "yyyy-mm-dd" as data, "2016-12-12T00:00:00" given.
     */
    public function testErrorDataIsMalformedWithISODate()
    {
        $this->execute([['a_date', Operators::BETWEEN, '2016-12-12T00:00:00']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_date" is not supported or does not support operator "CONTAINS"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['a_date', Operators::CONTAINS, '2017-02-07']]);
    }
}
