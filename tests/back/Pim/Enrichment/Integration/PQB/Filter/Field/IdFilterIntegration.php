<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Jullien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    private static $ids = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $foo = $this->createProduct('foo', []);
        $bar = $this->createProduct('bar', []);
        $baz = $this->createProduct('baz', []);
        $barista = $this->createProduct('BARISTA', []);
        $bazar = $this->createProduct('BAZAR', []);

        self::$ids['foo'] = (string) $foo->getId();
        self::$ids['bar'] = (string) $bar->getId();
        self::$ids['baz'] = (string) $baz->getId();
        self::$ids['barista'] = (string) $barista->getId();
        self::$ids['bazar'] = (string) $bazar->getId();
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['id', Operators::EQUALS, self::$ids['baz']]]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['id', Operators::EQUALS, $this->getUnknowRandomId()]]);
        $this->assert($result, []);
    }

    public function testOperatorNotEquals()
    {
        $result = $this->executeFilter([['id', Operators::NOT_EQUAL, $this->getUnknowRandomId()]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['id', Operators::NOT_EQUAL, self::$ids['baz']]]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorInList()
    {
        $result = $this->executeFilter([['id', Operators::IN_LIST, [self::$ids['baz'], self::$ids['foo']]]]);
        $this->assert($result, ['foo', 'baz']);

        $result = $this->executeFilter([['id', Operators::IN_LIST, [self::$ids['baz'], $this->getUnknowRandomId()]]]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['id', Operators::IN_LIST, [$this->getUnknowRandomId(), $this->getUnknowRandomId()]]]);
        $this->assert($result, []);
    }

    public function testOperatorNotInList()
    {
        $result = $this->executeFilter([['id', Operators::NOT_IN_LIST, [self::$ids['baz'], self::$ids['foo']]]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['id', Operators::NOT_IN_LIST, [self::$ids['baz'], $this->getUnknowRandomId()]]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR', 'foo']);

        $result = $this->executeFilter([['id', Operators::NOT_IN_LIST, [$this->getUnknowRandomId(), $this->getUnknowRandomId()]]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "id" expects a string as data, "array" given.');

        $this->executeFilter([['id', Operators::EQUALS, ['string']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "id" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['id', Operators::BETWEEN, 'foo']]);
    }

    public function testDataIsMalformedForOperatorInList()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "id" expects an array as data, "string" given.');

        $this->executeFilter([['id', Operators::IN_LIST, 'foo']]);
    }

    public function testDataIsNotAListOfStringForOperatorInList()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "id" expects an array with valid data, one of the value is not string.');

        $this->executeFilter([['id', Operators::IN_LIST, [12, 'foo']]]);
    }

    public function testDataIsMalformedForOperatorNotInList()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "id" expects an array as data, "string" given.');

        $this->executeFilter([['id', Operators::NOT_IN_LIST, 'foo']]);
    }

    public function testDataIsNotAListOfStringForOperatorNotInList()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "id" expects an array with valid data, one of the value is not string.');

        $this->executeFilter([['id', Operators::NOT_IN_LIST, [12, 'foo']]]);
    }

    /**
     * @return int
     */
    private function getUnknowRandomId()
    {
        do {
            $id = rand();
        } while (in_array($id, self::$ids));

        return (string) $id;
    }
}
