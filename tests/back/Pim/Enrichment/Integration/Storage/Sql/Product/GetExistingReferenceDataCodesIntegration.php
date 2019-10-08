<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetExistingReferenceDataCodes;
use Akeneo\Test\Integration\TestCase;

class GetExistingReferenceDataCodesIntegration extends TestCase
{
    function test_it_filters_non_existing_reference_data_codes()
    {
        $expected = ['aertex'];
        $actual = $this->getQuery()->fromReferenceDataNameAndCodes('fabrics', ['aertex', 'foo']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    function test_it_returns_nothing_if_no_reference_data_exists()
    {
        $expected = [];
        $actual = $this->getQuery()->fromReferenceDataNameAndCodes('fabrics', ['foo', 'bar']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    function test_it_returns_all_existing_reference_data()
    {
        $expected = ['aertex', 'airdura', 'airguard'];
        $actual = $this->getQuery()->fromReferenceDataNameAndCodes('fabrics', ['aertex', 'airguard', 'airdura']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): GetExistingReferenceDataCodes
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_existing_reference_data_codes');
    }
}
