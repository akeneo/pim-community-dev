<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilterIntegration extends AbstractFilterTestCase
{
    public function testOperatorInferior()
    {
        $result = $this->execute([['updated', Operators::LOWER_THAN, '2016-08-04 01:28:51']]);
        $this->assert($result, []);

        $result = $this->execute([['updated', Operators::LOWER_THAN, '2016-08-04 01:28:52']]);
        $this->assert($result, ['bar']);

        $result = $this->execute([['updated', Operators::LOWER_THAN, '2016-08-25 00:00:00']]);
        $this->assert($result, ['bar']);

        $result = $this->execute([['updated', Operators::LOWER_THAN, '2016-08-25 00:00:01']]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['updated', Operators::LOWER_THAN, '2016-08-29 00:00:01']]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['updated', Operators::EQUALS, '2016-08-04 01:28:52']]);
        $this->assert($result, []);

        $result = $this->execute([['updated', Operators::EQUALS, '2016-08-04 01:28:51']]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->execute([['updated', Operators::GREATER_THAN, '2016-08-04 01:28:51']]);
        $this->assert($result, ['baz', 'foo']);

        $result = $this->execute([['updated', Operators::GREATER_THAN, '2016-08-04 01:28:50']]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['updated', Operators::IS_EMPTY, []]]);
        $this->assert($result, []);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['updated', Operators::IS_NOT_EMPTY, new \DateTime()]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['updated', Operators::NOT_EQUAL, '2016-08-29 00:00:00']]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['updated', Operators::NOT_EQUAL, '2016-08-29 12:00:00']]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorBetween()
    {
        $result = $this->execute([['updated', Operators::BETWEEN, ['2016-08-04 01:28:51', '2016-08-25 00:00:00']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->execute([['updated', Operators::BETWEEN, ['2016-08-04 01:28:51', '2016-08-24 23:59:59']]]);
        $this->assert($result, ['bar']);

        $result = $this->execute([['updated', Operators::BETWEEN, ['2016-08-29 00:00:01', '2016-08-29 00:00:01']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->execute([['updated', Operators::NOT_BETWEEN, ['2016-08-04 01:28:51', '2016-08-25 00:00:00']]]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['updated', Operators::NOT_BETWEEN, ['2016-08-04 01:28:51', '2016-08-24 23:59:59']]]);
        $this->assert($result, ['baz', 'foo']);

        $result = $this->execute([['updated', Operators::NOT_BETWEEN, ['2016-08-29 00:00:01', '2016-08-29 00:00:01']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "updated" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd H:i:s".
     */
    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->execute([['updated', Operators::BETWEEN, []]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "updated" expects a string with the format "yyyy-mm-dd H:i:s" as data, "2016-12-12T00:00:00" given.
     */
    public function testErrorDataIsMalformedWithISODate()
    {
        $this->execute([['updated', Operators::BETWEEN, '2016-12-12T00:00:00']]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "updated" is not supported or does not support operator "IN CHILDREN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['updated', Operators::IN_CHILDREN_LIST, ['2016-08-29 00:00:01']]]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }
}
