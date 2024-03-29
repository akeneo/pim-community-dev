<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('foo', []);
        $this->createProduct('bar', []);
        $this->createProduct('baz', []);
        $this->createProduct('BARISTA', []);
        $this->createProduct('BAZAR', []);
        $this->createProduct('foo-bar', []);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['identifier', Operators::STARTS_WITH, 'ba']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['identifier', Operators::STARTS_WITH, 'bA']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::STARTS_WITH, 'ba']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::STARTS_WITH, 'bA']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['identifier', Operators::CONTAINS, 'a']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['identifier', Operators::CONTAINS, 'A']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::CONTAINS, 'a']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::CONTAINS, 'A']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);
    }

    public function testOperatorNotContains()
    {
        $result = $this->executeFilter([['identifier', Operators::DOES_NOT_CONTAIN, 'a']]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['identifier', Operators::DOES_NOT_CONTAIN, 'A']]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['sku', Operators::DOES_NOT_CONTAIN, 'a']]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['sku', Operators::DOES_NOT_CONTAIN, 'A']]);
        $this->assert($result, ['foo']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['identifier', Operators::EQUALS, 'baz']]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['identifier', Operators::EQUALS, 'bAz']]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['identifier', Operators::EQUALS, 'bazz']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['identifier', Operators::EQUALS, 'foo-bar']]);
        $this->assert($result, ['foo-bar']);

        $result = $this->executeFilter([['sku', Operators::EQUALS, 'bazz']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['sku', Operators::EQUALS, 'bAz']]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['sku', Operators::EQUALS, 'foo-bar']]);
        $this->assert($result, ['foo-bar']);
    }

    public function testOperatorNotEquals()
    {
        $result = $this->executeFilter([['identifier', Operators::NOT_EQUAL, 'bazz']]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['identifier', Operators::NOT_EQUAL, 'baz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['identifier', Operators::NOT_EQUAL, 'bAz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::NOT_EQUAL, 'bazz']]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::NOT_EQUAL, 'baz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::NOT_EQUAL, 'bAz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR', 'foo-bar']);
    }

    public function testOperatorInList()
    {
        $result = $this->executeFilter([['identifier', Operators::IN_LIST, ['baz', 'FOO', 'foo-bar']]]);
        $this->assert($result, ['foo', 'baz', 'foo-bar']);

        $result = $this->executeFilter([['identifier', Operators::IN_LIST, ['bazz', 'FOOO']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['sku', Operators::IN_LIST, ['baz', 'FOO', 'foo-bar']]]);
        $this->assert($result, ['foo', 'baz', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::IN_LIST, ['BAZZ', 'FOOO']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotInList()
    {
        $result = $this->executeFilter([['identifier', Operators::NOT_IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['identifier', Operators::NOT_IN_LIST, ['bazz', 'FOOO']]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::NOT_IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::NOT_IN_LIST, ['BAZZ', 'FOOO']]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['identifier', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);

        $result = $this->executeFilter([['sku', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo-bar']);
    }

    public function testOperatorEmpty()
    {
        $this->createProduct(null, [
            new SetTextValue('a_text', null, null, 'no_identifier')
        ]);
        $this->createProduct(null, [
            new SetTextValue('a_text', null, null, 'second_no_identifier')
        ]);

        $result = $this->executeFilter([['identifier', Operators::IS_EMPTY, null]]);
        $this->assertCount(2, $result);

        // Filtering on sku doesn't return anything because the products are not part of a family
        $result = $this->executeFilter([['sku', Operators::IS_EMPTY, null]]);
        $this->assertCount(0, $result);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku']
        ]);
        $this->createProduct(null, [
            new SetTextValue('a_text', null, null, 'third_no_identifier'),
            new SetFamily('a_family')
        ]);

        $result = $this->executeFilter([['sku', Operators::IS_EMPTY, null]]);
        $this->assertCount(1, $result);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "identifier" expects a string as data, "array" given.');

        $this->executeFilter([['identifier', Operators::STARTS_WITH, ['string']]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "identifier" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['identifier', Operators::BETWEEN, 'foo']]);
    }

    public function testDataIsMalformedForOperatorInList()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "identifier" expects an array as data, "string" given.');

        $this->executeFilter([['identifier', Operators::IN_LIST, 'foo']]);
    }

    public function testDataIsMalformedForOperatorNotInList()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "identifier" expects an array as data, "string" given.');

        $this->executeFilter([['identifier', Operators::NOT_IN_LIST, 'foo']]);
    }

    public function testErrorDataIsMalformedWithAttributeIdentifierCode()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "identifier" expects a string as data, "array" given.');

        $this->executeFilter([['identifier', Operators::STARTS_WITH, ['string']]]);
    }

    public function testErrorDataIsNull()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "identifier" expects a string as data, "NULL" given.');

        $this->executeFilter([['identifier', Operators::EQUALS, null]]);
    }
}
