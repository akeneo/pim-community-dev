<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GenerateHeadersFromAttributeCodesIntegration extends TestCase
{
    public function test_generate_headers_from_attributes()
    {
        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_attribute_codes');
        $headers = $query(['sku', 'a_metric', 'a_localizable_image', 'a_scopable_price', 'a_regexp'], 'ecommerce', ['en_US']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader(
                'a_metric',
                false,
                'ecommerce',
                false,
                ['en_US'],
                false,
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
                'a_scopable_price',
                true,
                'ecommerce',
                false,
                ['en_US'],
                false,
                false,
                true,
                ['USD', 'EUR'],
                ['USD', 'EUR']
            ),
            new FlatFileHeader(
                'a_regexp',
                false,
                'ecommerce',
                false,
                ['en_US'],
                false,
                false,
                false,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY'],
                true,
                ['en_US']
            ),
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

