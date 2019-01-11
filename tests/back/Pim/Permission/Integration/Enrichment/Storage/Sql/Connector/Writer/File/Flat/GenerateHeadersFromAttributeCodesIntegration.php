<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Connector\Writer\File\Flat;

use AkeneoTestEnterprise\Pim\Permission\Integration\Security\AbstractSecurityTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat\AssertHeaders;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;

class GenerateHeadersFromAttributeCodesIntegration extends AbstractSecurityTestCase
{
    public function test_generate_headers_from_attributes_with_no_user_defined()
    {
        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_attribute_codes');
        $headers = $query(['sku', 'a_metric', 'a_localizable_image', 'a_scopable_price', 'a_regexp', 'a_multi_select'], 'ecommerce', ['en_US', 'de_DE']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader(
                'a_metric',
                false,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
                false,
                true
            ),
            new FlatFileHeader(
                'a_localizable_image',
                false,
                'ecommerce',
                true,
                ['en_US', 'de_DE'],
                true
            ),
            new FlatFileHeader(
                'a_scopable_price',
                true,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
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
                ['en_US', 'de_DE'],
                false,
                false,
                false,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY'],
                true,
                ['en_US']
            ),
            new FlatFileHeader(
                'a_multi_select',
                false,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
                false,
                false,
                false,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY']
            ),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }

    public function test_generate_headers_from_attributes_with_full_access()
    {
        $this->generateToken('admin');

        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_attribute_codes');
        $headers = $query(['sku', 'a_metric', 'a_localizable_image', 'a_scopable_price', 'a_regexp', 'a_multi_select'], 'ecommerce', ['en_US', 'de_DE']);

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
                ['en_US', 'de_DE'],
                true
            ),
            new FlatFileHeader(
                'a_scopable_price',
                true,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
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
                ['en_US', 'de_DE'],
                false,
                false,
                false,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY'],
                true,
                ['en_US']
            ),
            new FlatFileHeader(
                'a_multi_select',
                false,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
                false,
                false,
                false,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY']
            ),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }

    public function test_generate_headers_from_attributes_with_limited_access_on_attribute_groups()
    {
        $this->generateToken('mary');

        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_attribute_codes');
        $headers = $query(['sku', 'a_metric', 'a_localizable_image', 'a_scopable_price', 'a_regexp', 'a_multi_select'], 'ecommerce', ['en_US']);

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

    public function test_generate_headers_from_attributes_with_limited_access_on_locales()
    {
        $this->generateToken('mary');

        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_attribute_codes');
        $headers = $query(['sku', 'a_metric', 'a_localizable_image', 'a_scopable_price', 'a_regexp', 'a_multi_select'], 'ecommerce', ['en_US', 'de_DE']);

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
}
