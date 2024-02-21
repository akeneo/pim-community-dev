<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GenerateHeadersFromFamilyCodesIntegration extends TestCase
{
    public function test_generate_headers_from_attributes()
    {
        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_family_codes');
        $headers = $query(['familyA1', 'familyA2'], 'ecommerce', ['en_US']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader('a_date'),
            new FlatFileHeader(
                'a_file',
                false,
                'ecommerce',
                false,
                ['en_US'],
                true
            ),
            new FlatFileHeader(
                'a_localizable_image',
                false,
                'ecommerce',
                true,
                ['en_US'],
                true
            ),
            new FlatFileHeader(
                'a_metric',
                false,
                'ecommerce',
                false,
                ['en_US'],
                false,
                true
            ),
            new FlatFileHeader('a_number_float'),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

