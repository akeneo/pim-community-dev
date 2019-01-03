<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Connector\Writer\File\Flat;

use AkeneoTestEnterprise\Pim\Permission\Integration\Security\AbstractSecurityTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat\AssertHeaders;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;

class GenerateHeadersFromFamilyCodesIntegration extends AbstractSecurityTestCase
{
    public function test_generate_headers_from_attributes_with_no_user_defined()
    {
        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_family_codes');
        $headers = $query(['familyA'], 'ecommerce', ['en_US', 'de_DE']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader('a_date'),
            new FlatFileHeader('a_file', false, 'ecommerce', false, ['en_US', 'de_DE'], true),
            new FlatFileHeader('an_image', false, 'ecommerce', false, ['en_US', 'de_DE'], true),
            new FlatFileHeader('a_metric', false, 'ecommerce', false, ['en_US', 'de_DE'], false, true),
            new FlatFileHeader('a_multi_select'),
            new FlatFileHeader('a_number_float'),
            new FlatFileHeader('a_number_float_negative'),
            new FlatFileHeader('a_number_integer'),
            new FlatFileHeader(
                'a_price',
                false,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
                false,
                false,
                true,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY']
            ),
            new FlatFileHeader('a_ref_data_multi_select'),
            new FlatFileHeader('a_ref_data_simple_select'),
            new FlatFileHeader('a_simple_select'),
            new FlatFileHeader('a_text'),
            new FlatFileHeader('a_text_area'),
            new FlatFileHeader('a_yes_no'),
            new FlatFileHeader('a_localizable_image', false, 'ecommerce', true, ['en_US', 'de_DE'], true),
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
            new FlatFileHeader('a_localized_and_scopable_text_area', true, 'ecommerce', true, ['en_US', 'de_DE']),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }

    public function test_generate_headers_from_attributes_with_full_access()
    {
        $this->generateToken('admin');

        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_family_codes');
        $headers = $query(['familyA'], 'ecommerce', ['en_US', 'de_DE']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader('a_date'),
            new FlatFileHeader('a_file', false, 'ecommerce', false, ['en_US', 'de_DE'], true),
            new FlatFileHeader('an_image', false, 'ecommerce', false, ['en_US', 'de_DE'], true),
            new FlatFileHeader('a_metric', false, 'ecommerce', false, ['en_US', 'de_DE'], false, true),
            new FlatFileHeader('a_multi_select'),
            new FlatFileHeader('a_number_float'),
            new FlatFileHeader('a_number_float_negative'),
            new FlatFileHeader('a_number_integer'),
            new FlatFileHeader(
                'a_price',
                false,
                'ecommerce',
                false,
                ['en_US', 'de_DE'],
                false,
                false,
                true,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY']
            ),
            new FlatFileHeader('a_ref_data_multi_select'),
            new FlatFileHeader('a_ref_data_simple_select'),
            new FlatFileHeader('a_simple_select'),
            new FlatFileHeader('a_text'),
            new FlatFileHeader('a_text_area'),
            new FlatFileHeader('a_yes_no'),
            new FlatFileHeader('a_localizable_image', false, 'ecommerce', true, ['en_US', 'de_DE'], true),
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
            new FlatFileHeader('a_localized_and_scopable_text_area', true, 'ecommerce', true, ['en_US', 'de_DE']),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }

    public function test_generate_headers_from_attributes_with_limited_access_on_attribute_groups()
    {
        $this->generateToken('mary');

        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_family_codes');
        $headers = $query(['familyA'], 'ecommerce', ['en_US']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader('a_date'),
            new FlatFileHeader('a_file', false, 'ecommerce', false, ['en_US'], true),
            new FlatFileHeader('an_image', false, 'ecommerce', false, ['en_US'], true),
            new FlatFileHeader('a_metric', false, 'ecommerce', false, ['en_US'], false, true),
            new FlatFileHeader('a_number_float'),
            new FlatFileHeader('a_number_float_negative'),
            new FlatFileHeader('a_number_integer'),
            new FlatFileHeader(
                'a_price',
                false,
                'ecommerce',
                false,
                ['en_US'],
                false,
                false,
                true,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY']
            ),
            new FlatFileHeader('a_ref_data_multi_select'),
            new FlatFileHeader('a_ref_data_simple_select'),
            new FlatFileHeader('a_simple_select'),
            new FlatFileHeader('a_text'),
            new FlatFileHeader('a_text_area'),
            new FlatFileHeader('a_yes_no'),
            new FlatFileHeader('a_localizable_image', false, 'ecommerce', true, ['en_US'], true),
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
            new FlatFileHeader('a_localized_and_scopable_text_area', true, 'ecommerce', true, ['en_US']),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }

    public function test_generate_headers_from_attributes_with_limited_access_on_locales()
    {
        $this->generateToken('mary');

        $query = $this->get('akeneo.pim.enrichment.connector.write.file.flat.generate_headers_from_family_codes');
        $headers = $query(['familyA'], 'ecommerce', ['en_US', 'de_DE']);

        $expectedHeaders = [
            new FlatFileHeader('sku'),
            new FlatFileHeader('a_date'),
            new FlatFileHeader('a_file', false, 'ecommerce', false, ['en_US'], true),
            new FlatFileHeader('an_image', false, 'ecommerce', false, ['en_US'], true),
            new FlatFileHeader('a_metric', false, 'ecommerce', false, ['en_US'], false, true),
            new FlatFileHeader('a_number_float'),
            new FlatFileHeader('a_number_float_negative'),
            new FlatFileHeader('a_number_integer'),
            new FlatFileHeader(
                'a_price',
                false,
                'ecommerce',
                false,
                ['en_US'],
                false,
                false,
                true,
                ['USD', 'EUR'],
                ['USD', 'EUR', 'CNY']
            ),
            new FlatFileHeader('a_ref_data_multi_select'),
            new FlatFileHeader('a_ref_data_simple_select'),
            new FlatFileHeader('a_simple_select'),
            new FlatFileHeader('a_text'),
            new FlatFileHeader('a_text_area'),
            new FlatFileHeader('a_yes_no'),
            new FlatFileHeader('a_localizable_image', false, 'ecommerce', true, ['en_US'], true),
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
            new FlatFileHeader('a_localized_and_scopable_text_area', true, 'ecommerce', true, ['en_US']),
        ];

        AssertHeaders::same($expectedHeaders, $headers);
    }
}
