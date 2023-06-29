<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelIdentifierFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
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

        $this->createProductModel('foo');
        $this->createProductModel('Bang');
        $this->createProductModel('plop');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['identifier', Operators::STARTS_WITH, 'ba']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'Bang']);

        $result = $this->executeFilter([['identifier', Operators::STARTS_WITH, 'bA']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'Bang']);

        $result = $this->executeFilter([['sku', Operators::STARTS_WITH, 'ba']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::STARTS_WITH, 'bA']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['identifier', Operators::CONTAINS, 'a']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'Bang']);

        $result = $this->executeFilter([['identifier', Operators::CONTAINS, 'A']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR', 'Bang']);

        $result = $this->executeFilter([['sku', Operators::CONTAINS, 'a']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::CONTAINS, 'A']]);
        $this->assert($result, ['bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorNotContains()
    {
        $result = $this->executeFilter([['identifier', Operators::DOES_NOT_CONTAIN, 'a']]);
        $this->assert($result, ['foo', 'foo', 'plop']);

        $result = $this->executeFilter([['identifier', Operators::DOES_NOT_CONTAIN, 'A']]);
        $this->assert($result, ['foo', 'foo', 'plop']);

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

        $result = $this->executeFilter([['identifier', Operators::EQUALS, 'foo']]);
        $this->assert($result, ['foo', 'foo']);

        $result = $this->executeFilter([['sku', Operators::EQUALS, 'bazz']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['sku', Operators::EQUALS, 'bAz']]);
        $this->assert($result, ['baz']);
    }

    public function testOperatorNotEquals()
    {
        $result = $this->executeFilter([['identifier', Operators::NOT_EQUAL, 'bazz']]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo', 'Bang', 'plop']);

        $result = $this->executeFilter([['identifier', Operators::NOT_EQUAL, 'baz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR', 'foo', 'Bang', 'plop']);

        $result = $this->executeFilter([['identifier', Operators::NOT_EQUAL, 'bAz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR', 'foo', 'Bang', 'plop']);

        $result = $this->executeFilter([['sku', Operators::NOT_EQUAL, 'bazz']]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::NOT_EQUAL, 'baz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::NOT_EQUAL, 'bAz']]);
        $this->assert($result, ['foo', 'bar', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorInList()
    {
        $result = $this->executeFilter([['identifier', Operators::IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['foo', 'baz', 'foo']);

        $result = $this->executeFilter([['identifier', Operators::IN_LIST, ['bazz', 'FOOO']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['sku', Operators::IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['foo', 'baz']);

        $result = $this->executeFilter([['sku', Operators::IN_LIST, ['BAZZ', 'FOOO']]]);
        $this->assert($result, []);
    }

    public function testOperatorNotInList()
    {
        $result = $this->executeFilter([['identifier', Operators::NOT_IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR', 'Bang', 'plop']);

        $result = $this->executeFilter([['identifier', Operators::NOT_IN_LIST, ['bazz', 'FOOO']]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo', 'Bang', 'plop']);

        $result = $this->executeFilter([['sku', Operators::NOT_IN_LIST, ['baz', 'FOO']]]);
        $this->assert($result, ['bar', 'BARISTA', 'BAZAR']);

        $result = $this->executeFilter([['sku', Operators::NOT_IN_LIST, ['BAZZ', 'FOOO']]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['identifier', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['foo', 'bar', 'baz', 'BARISTA', 'BAZAR', 'foo', 'Bang', 'plop']);
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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProductModel(string $code): ProductModelId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($code)
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $this->get(ProductModelIdFactory::class)->create((string) $productModel->getId());
    }
}
