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
                'tablet'    => ['de_DE' => 89, 'en_US' => 100, 'fr_FR' => 100],
            ],
            'values'       => [
                'a_date-date'                                    => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_file-media'                                   => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_localizable_image-media'                      => [
                    'en_US' => [
                        '<all_channels>' => null,
                    ],
                    'fr_FR' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_localized_and_scopable_text_area-text'        => [
                    'en_US' => [
                        'ecommerce' => null,
                        'tablet'    => null,
                    ],
                    'fr_FR' => [
                        'tablet' => null,
                    ],
                ],
                'a_metric-metric'                                => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_metric_negative-metric'                       => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_metric_without_decimal-metric'                => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_metric_without_decimal_negative-metric'       => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_multi_select-options'                         => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_number_float-decimal'                         => [
                    '<all_locales>' => [
                        '<all_channels>' => '12.5678',
                    ],
                ],
                'a_number_float_negative-decimal'                => [
                    '<all_locales>' => [
                        '<all_channels>' => '-99.8732',
                    ],
                ],
                'a_number_integer-decimal'                       => [
                    '<all_locales>' => [
                        '<all_channels>' => '42',
                    ],
                ],
                'a_number_integer_negative-decimal'              => [
                    '<all_locales>' => [
                        '<all_channels>' => '-42',
                    ],
                ],
                'a_price-prices'                                 => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_price_without_decimal-prices'                 => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_ref_data_multi_select-reference_data_options' => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_ref_data_simple_select-reference_data_option' => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_scopable_price-prices'                        => [
                    '<all_locales>' => [
                        'ecommerce' => null,
                        'tablet'    => null,
                    ],
                ],
                'a_simple_select-option'                         => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_text-varchar'                                 => [
                    '<all_locales>' => [
                        '<all_channels>' => 'this is a text',
                    ],
                ],
                'a_text_area-text'                               => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'a_yes_no-boolean'                               => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
                'an_image-media'                                 => [
                    '<all_locales>' => [
                        '<all_channels>' => null,
                    ],
                ],
            ],
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
