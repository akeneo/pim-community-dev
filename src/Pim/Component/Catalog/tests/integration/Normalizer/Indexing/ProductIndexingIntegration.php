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
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'identifier'   => 'bar',
            'created'      => $date->format('c'),
            'updated'      => $date->format('c'),
            'family'       => null,
            'enabled'      => false,
            'categories'   => [],
            'groups'       => [],
            'completeness' => [],
            'values'       => [],
        ];

        $this->assertIndexingFormat('bar', $expected);
    }

    public function testEmptyEnabledProduct()
    {
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'identifier'   => 'baz',
            'created'      => $date->format('c'),
            'updated'      => $date->format('c'),
            'family'       => null,
            'enabled'      => true,
            'categories'   => [],
            'groups'       => [],
            'completeness' => [],
            'values'       => [],
        ];

        $this->assertIndexingFormat('baz', $expected);
    }

    public function testProductWithAllAttributes()
    {
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'identifier'   => 'foo',
            'created'      => $date->format('c'),
            'updated'      => $date->format('c'),
            'family'       => 'familyA',
            'enabled'      => true,
            'categories'   => ['categoryA1', 'categoryB'],
            'groups'       => ['groupA', 'groupB', 'variantA'],
            'completeness' => [
                'ecommerce' => ['en_US' => 100],
                'tablet'    => ['de_DE' => 89, 'en_US' => 100, 'fr_FR' => 100]
            ],
            'values'       => []
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
