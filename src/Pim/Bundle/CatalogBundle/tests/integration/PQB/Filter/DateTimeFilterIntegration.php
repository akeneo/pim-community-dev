<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * @var array
     */
    protected static $createdDates = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        static::$createdDates['before_first'] = new \DateTime('now', new \DateTimeZone('UTC'));
        sleep(2);
        $this->createProduct('foo', []);
        sleep(2);
        static::$createdDates['before_second'] = new \DateTime('now', new \DateTimeZone('UTC'));
        sleep(2);
        $this->createProduct('bar', []);
        sleep(2);
        static::$createdDates['before_third'] = new \DateTime('now', new \DateTimeZone('UTC'));
        sleep(2);
        $this->createProduct('baz', []);
        sleep(2);
        static::$createdDates['after_all'] = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, static::$createdDates['before_first']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, static::$createdDates['before_second']]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, static::$createdDates['before_third']]]);
        $this->assert($result, ['foo', 'bar']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, static::$createdDates['after_all']]]);
        $this->assert($result, ['foo', 'bar', 'baz']);
    }

    public function testOperatorEquals()
    {
        $barProduct = $this->get('pim_api.repository.product')->findOneByIdentifier('bar');

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $result = $this->executeFilter([['updated', Operators::EQUALS, $now->format('Y-m-d H:i:s')]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['updated', Operators::EQUALS, $barProduct->getUpdated()]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, static::$createdDates['before_second']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, static::$createdDates['before_first']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['updated', Operators::IS_EMPTY, null]]);
        $this->assert($result, []);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['updated', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorDifferent()
    {
        $barProduct = $this->get('pim_api.repository.product')->findOneByIdentifier('bar');
        $updatedAt = $barProduct->getUpdated();
        $updatedAt->setTimezone(new \DateTimeZone('UTC'));

        $result = $this->executeFilter([['updated', Operators::NOT_EQUAL, $updatedAt]]);
        $this->assert($result, ['foo', 'baz']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_EQUAL, $currentDate->format('Y-m-d H:i:s')]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorBetween()
    {
        $result = $this->executeFilter([['updated', Operators::BETWEEN, [static::$createdDates['before_second'], static::$createdDates['after_all']]]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::BETWEEN, [static::$createdDates['before_second'], static::$createdDates['before_third']]]]);
        $this->assert($result, ['bar']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::BETWEEN, [static::$createdDates['after_all'], $currentDate]]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [static::$createdDates['before_second'], static::$createdDates['after_all']]]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [static::$createdDates['before_second'], static::$createdDates['before_third']]]]);
        $this->assert($result, ['baz', 'foo']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [static::$createdDates['after_all'], $currentDate]]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "updated" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd H:i:s".
     */
    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->executeFilter([['updated', Operators::BETWEEN, []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "updated" expects a string with the format "yyyy-mm-dd H:i:s" as data, "2016-12-12T00:00:00" given.
     */
    public function testErrorDataIsMalformedWithISODate()
    {
        $this->executeFilter([['updated', Operators::EQUALS, '2016-12-12T00:00:00']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "updated" is not supported or does not support operator "IN CHILDREN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['updated', Operators::IN_CHILDREN_LIST, ['2016-08-29 00:00:01']]]);
    }
}
