<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->resetIndex();

            $this->createProduct('foo', []);
            $this->createProduct('bar', []);
            $this->createProduct('baz', []);
            $this->createProduct('BARISTA', []);
            $this->createProduct('BAZAR', []);
        }
    }

    public function testOperatorStartsWith()
    {
        $result = $this->execute([['identifier', Operators::STARTS_WITH, 'ba']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['identifier', Operators::STARTS_WITH, 'bA']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::STARTS_WITH, 'ba']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::STARTS_WITH, 'bA']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['identifier', Operators::CONTAINS, 'a']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['identifier', Operators::CONTAINS, 'A']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::CONTAINS, 'a']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::CONTAINS, 'A']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorNotContains()
    {
        $result = $this->execute([['identifier', Operators::DOES_NOT_CONTAIN, 'a']]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['identifier', Operators::DOES_NOT_CONTAIN, 'A']]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['sku', Operators::DOES_NOT_CONTAIN, 'a']]);
        $this->assert($result, ['foo']);

        $result = $this->execute([['sku', Operators::DOES_NOT_CONTAIN, 'A']]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['identifier', Operators::EQUALS, 'baz']]);
        $this->assert($result, ['baz']);

        $result = $this->execute([['identifier', Operators::EQUALS, 'bAz']]);
        $this->assert($result, ['baz']);

        $result = $this->execute([['identifier', Operators::EQUALS, 'bazz']]);
        $this->assert($result, []);

        $result = $this->execute([['sku', Operators::EQUALS, 'bazz']]);
        $this->assert($result, []);

        $result = $this->execute([['sku', Operators::EQUALS, 'bAz']]);
        $this->assert($result, ['baz']);
    }

    public function testOperatorNotEquals()
    {
        $result = $this->execute([['identifier', Operators::NOT_EQUAL, 'bazz']]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['identifier', Operators::NOT_EQUAL, 'baz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['identifier', Operators::NOT_EQUAL, 'bAz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::NOT_EQUAL, 'bazz']]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::NOT_EQUAL, 'baz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::NOT_EQUAL, 'bAz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorInList()
    {
        $result = $this->execute([['identifier', Operators::IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['foo', 'baz']);

        $result = $this->execute([['identifier', Operators::IN_LIST, ['bazz', 'FOOO']]]);
        $this->assert($result, []);

        $result = $this->execute([['sku', Operators::IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['foo', 'baz']);

        $result = $this->execute([['sku', Operators::IN_LIST, ['BAZZ', 'FOOO']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotInList()
    {
        $result = $this->execute([['identifier', Operators::NOT_IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['identifier', Operators::NOT_IN_LIST, ['bazz', 'FOOO']]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::NOT_IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR']);

        $result = $this->execute([['sku', Operators::NOT_IN_LIST, ['BAZZ', 'FOOO']]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "identifier" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['identifier', Operators::STARTS_WITH, ['string']]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "identifier" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['identifier', Operators::BETWEEN, 'foo']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "identifier" expects an array as data, "string" given.
     */
    public function testDataIsMalformedForOperatorInList()
    {
        $this->execute([['identifier', Operators::IN_LIST, 'foo']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "identifier" expects an array as data, "string" given.
     */
    public function testDataIsMalformedForOperatorNotInList()
    {
        $this->execute([['identifier', Operators::NOT_IN_LIST, 'foo']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "identifier" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformedWithAttributeIdentifierCode()
    {
        $this->execute([['identifier', Operators::STARTS_WITH, ['string']]]);
    }
}
