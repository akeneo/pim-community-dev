<?php

namespace tests\integration\Pim\Component\Catalog\Normalizer\Indexing;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;

/**
 * Integration tests to verify data from database are well formatted in the indexing format
 */
class ProductIndexingIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }

    public function testEmptyDisabledProduct()
    {
        $expected = [
            'identifier' => 'bar',
            'values'     => [],
        ];

        $this->assertIndexingFormat('bar', $expected);
    }

    public function testEmptyEnabledProduct()
    {
        $expected = [
            'identifier' => 'baz',
            'values'     => [],
        ];

        $this->assertIndexingFormat('baz', $expected);
    }

    public function testProductWithAllAttributes()
    {
        $expected = [
            'identifier' => 'foo',
            'values'     => [],
        ];

        $this->assertIndexingFormat('foo', $expected);
    }

    /**
     * @param string $identifier
     * @param array  $expected
     */
    private function assertIndexingFormat($identifier, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier($identifier);

        $serializer = $this->get('pim_serializer');
        $actual = $serializer->normalize($product, 'indexing');

        NormalizedProductCleaner::clean($actual);
        NormalizedProductCleaner::clean($expected);

        $this->assertSame($expected, $actual);
    }
}
