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
                    '<all_channels>' => [
                        '<all_locales>' => '2016-06-13',
                    ],
                ],
                'a_file-media'                                   => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
                'a_localizable_image-media'                      => [
                    '<all_channels>' => [
                        'en_US' => null,
                        'fr_FR' => null,
                    ],
                ],
                'a_localized_and_scopable_text_area-text'        => [
                    'ecommerce' => [
                        'en_US' => 'a text area for ecommerce in English',
                    ],
                    'tablet'    => [
                        'en_US' => 'a text area for tablets in English',
                        'fr_FR' => 'une zone de texte pour les tablettes en français',
                    ],
                ],
                'a_metric-metric'                                => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '9.8765432198712E+14',
                            'base_unit' => 'WATT',
                            'data'      => '987654321987.1234',
                            'unit'      => 'KILOWATT',
                        ],
                    ],
                ],
                'a_metric_negative-metric'                       => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '252.65',
                            'base_unit' => 'KELVIN',
                            'data'      => '-20.5000',
                            'unit'      => 'CELSIUS',
                        ],
                    ],
                ],
                'a_metric_without_decimal-metric'                => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '0.98',
                            'data'    => '98',
                            'base_unit' => 'METER',
                            'unit'      => 'CENTIMETER',
                        ],
                    ],
                ],
                'a_metric_without_decimal_negative-metric'       => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '253.15',
                            'data'    => '-20',
                            'base_unit' => 'KELVIN',
                            'unit'      => 'CELSIUS',
                        ],
                    ],
                ],
                'a_multi_select-options'                         => [
                    '<all_channels>' => [
                        '<all_locales>' => ['optionA', 'optionB'],
                    ],
                ],
                'a_number_float-decimal'                         => [
                    '<all_channels>' => [
                        '<all_locales>' => '12.5678',
                    ],
                ],
                'a_number_float_negative-decimal'                => [
                    '<all_channels>' => [
                        '<all_locales>' => '-99.8732',
                    ],
                ],
                'a_number_integer-decimal'                       => [
                    '<all_channels>' => [
                        '<all_locales>' => '42',
                    ],
                ],
                'a_number_integer_negative-decimal'              => [
                    '<all_channels>' => [
                        '<all_locales>' => '-42',
                    ],
                ],
                'a_price-prices'                                 => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'EUR' => '56.53',
                            'USD' => '45.00',
                        ],
                    ],
                ],
                'a_price_without_decimal-prices'                 => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'EUR' => '56',
                            'USD' => '-45',
                        ],
                    ],
                ],
                'a_ref_data_multi_select-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
                'a_ref_data_simple_select-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
                'a_scopable_price-prices'                        => [
                    'ecommerce' => [
                        '<all_locales>' => [
                            'EUR' => '15.00',
                            'USD' => '20.00',
                        ],
                    ],
                    'tablet'    => [
                        '<all_locales>' => [
                            'EUR' => '17.00',
                            'USD' => '24.00',
                        ],
                    ],
                ],
                'a_simple_select-option'                         => [
                    '<all_channels>' => [
                        '<all_locales>' => 'optionB',
                    ],
                ],
                'a_text-varchar'                                 => [
                    '<all_channels>' => [
                        '<all_locales>' => 'this is a text',
                    ],
                ],
                '123-varchar'                                    => [
                    '<all_channels>' => [
                        '<all_locales>' => 'a text for an attribute with numerical code',
                    ],
                ],
                'a_text_area-text'                               => [
                    '<all_channels>' => [
                        '<all_locales>' => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no-boolean'                               => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
                    ],
                ],
                'an_image-media'                                 => [
                    '<all_channels>' => [
                        '<all_locales>' => null,
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
