<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;

/**
 * Integration tests to check that the projection of the product is correctly fetched from the database.
 * A test exists also in EE to check that it fetches the required properties.
 *
 * @group ce
 */
class GetElasticsearchProductProjectionIntegration extends TestCase
{
    public function test_it_gets_product_projection_of_a_variant_product()
    {
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'id' => 'product_50',
            'identifier' => 'qux',
            'created' => $date->format('c'),
            'updated' => $date->format('c'),
            'family' => [
                'code' => 'familyA',
                'labels' => [
                    'de_DE' => null,
                    'en_US' => 'A family A',
                    'fr_FR' => 'Une famille A',
                ],
            ],
            'enabled' => true,
            'categories' => ['categoryA', 'categoryA1', 'categoryA2', 'categoryB'],
            'categories_of_ancestors' => ['categoryA', 'categoryA1', 'categoryB'],
            'groups' => [],
            'completeness' => [],
            'family_variant' => 'familyVariantA1',
            'parent' => 'quux',
            'values' => [
                'a_text-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'this is a text',
                    ],
                ],
                'a_simple_select-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'optionB',
                    ],
                ],
                'a_yes_no-boolean' => [
                    '<all_channels>' => [
                        '<all_locales>' => true,
                    ],
                ],
                'sku-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'qux',
                    ],
                ],
            ],
            'ancestors' => [
                'ids' => ['product_model_151', 'product_model_150'],
                'codes' => ['quux', 'qux'],
                'labels' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'qux',
                    ],
                ],
            ],
            'label' => [
                '<all_channels>' => [
                    '<all_locales>' => 'qux',
                ],
            ],
            'document_type' => ProductInterface::class,
            'attributes_of_ancestors' => [
                'a_date',
                'a_file',
                'a_localizable_image',
                'a_localized_and_scopable_text_area',
                'a_metric',
                'a_multi_select',
                'a_number_float',
                'a_number_float_negative',
                'a_number_integer',
                'a_price',
                'a_ref_data_multi_select',
                'a_ref_data_simple_select',
                'a_scopable_price',
                'a_simple_select',
                'a_text',
                'a_text_area',
                'a_yes_no',
                'an_image',
                'sku',
            ],
            'attributes_for_this_level' => ['a_yes_no', 'sku'],
            'associations' => null,
        ];

        $this->assertProductIndexingFormat('qux', $expected);
    }

    public function test_it_gets_product_projection_of_a_product_without_family_and_without_group_and_without_values()
    {
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'id' => 'product_47',
            'identifier' => 'bar',
            'created' => $date->format('c'),
            'updated' => $date->format('c'),
            'family' => null,
            'enabled' => false,
            'categories' => [],
            'categories_of_ancestors' => [],
            'groups' => [],
            'completeness' => [],
            'family_variant' => null,
            'parent' => null,
            'values' => [
                'sku-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar'
                    ]
                ]
            ],
            'ancestors' => [
                'ids' => [],
                'codes' => [],
                'labels' => [],
            ],
            'label' => [],
            'document_type' => ProductInterface::class,
            'attributes_of_ancestors' => [],
            'attributes_for_this_level' => ['sku'],
        ];

        $this->assertProductIndexingFormat('bar', $expected);
    }

    public function test_it_gets_product_projection_of_a_product_with_all_attribute_types()
    {
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'id' => 'product_49',
            'identifier' => 'foo',
            'created' => $date->format('c'),
            'updated' => $date->format('c'),
            'family' => [
                'code' => 'familyA',
                'labels' => [
                    'de_DE' => null,
                    'en_US' => 'A family A',
                    'fr_FR' => 'Une famille A',
                ],
            ],
            'enabled' => true,
            'categories' => ['categoryA1', 'categoryB'],
            'categories_of_ancestors' => [],
            'groups' => ['groupA', 'groupB'],
            'in_group' => [
                'groupA' => true,
                'groupB' => true,
            ],
            'completeness' => [
                'ecommerce' => ['en_US' => 100],
                'tablet' => ['de_DE' => 89, 'en_US' => 100, 'fr_FR' => 100],
            ],
            'family_variant' => null,
            'parent' => null,
            'values' => [
                'a_date-date' => [
                    '<all_channels>' => [
                        '<all_locales>' => '2016-06-13',
                    ],
                ],
                'a_file-media' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'extension' => 'txt',
                            'hash' => '6545089471ba53d660d22d7b8dc8dd67904b1e28',
                            'key' => '8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt',
                            'mime_type' => 'text/plain',
                            'original_filename' => 'fileA.txt',
                            'size' => 1048576,
                            'storage' => 'catalogStorage',

                        ],
                    ],
                ],
                'a_localizable_image-media' => [
                    '<all_channels>' => [
                        'en_US' => [
                            'extension' => 'jpg',
                            'hash' => '16850b6741c6e0d7622edb29465488571a2e63df',
                            'key' => '7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg',
                            'mime_type' => 'image/jpeg',
                            'original_filename' => 'imageB-en_US.jpg',
                            'size' => 1048576,
                            'storage' => 'catalogStorage',
                        ],
                        'fr_FR' => [
                            'extension' => 'jpg',
                            'hash' => '058c6f380b0888afadf7341f8baaf58b538e5774',
                            'key' => '0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg',
                            'mime_type' => 'image/jpeg',
                            'original_filename' => 'imageB-fr_FR.jpg',
                            'size' => 1048576,
                            'storage' => 'catalogStorage',
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area-textarea' => [
                    'ecommerce' => [
                        'en_US' => 'a text area for ecommerce in English',
                    ],
                    'tablet' => [
                        'en_US' => 'a text area for tablets in English',
                        'fr_FR' => 'une zone de texte pour les tablettes en français',
                    ],
                ],
                'a_metric-metric' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '987654321987123.400000000000',
                            'base_unit' => 'WATT',
                            'data' => '987654321987.1234',
                            'unit' => 'KILOWATT',
                        ],
                    ],
                ],
                'a_metric_negative-metric' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '252.650000000000',
                            'base_unit' => 'KELVIN',
                            'data' => '-20.5000',
                            'unit' => 'CELSIUS',
                        ],
                    ],
                ],
                'a_metric_without_decimal-metric' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '0.980000000000',
                            'data' => '98',
                            'base_unit' => 'METER',
                            'unit' => 'CENTIMETER',
                        ],
                    ],
                ],
                'a_metric_without_decimal_negative-metric' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'base_data' => '253.150000000000',
                            'data' => '-20',
                            'base_unit' => 'KELVIN',
                            'unit' => 'CELSIUS',
                        ],
                    ],
                ],
                'a_multi_select-options' => [
                    '<all_channels>' => [
                        '<all_locales>' => ['optionA', 'optionB'],
                    ],
                ],
                'a_number_float-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => '12.5678',
                    ],
                ],
                'a_number_float_negative-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => '-99.8732',
                    ],
                ],
                'a_number_integer-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => '42',
                    ],
                ],
                'a_number_integer_negative-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => '-42',
                    ],
                ],
                'a_price-prices' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'EUR' => '56.53',
                            'USD' => '45.00',
                        ],
                    ],
                ],
                'a_price_without_decimal-prices' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'EUR' => '56',
                            'USD' => '-45',
                        ],
                    ],
                ],
                'a_ref_data_multi_select-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'fabricA',
                            'fabricB',
                        ],
                    ],
                ],
                'a_ref_data_simple_select-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'colorB',
                    ],
                ],
                'a_scopable_price-prices' => [
                    'ecommerce' => [
                        '<all_locales>' => [
                            'USD' => '20.00',
                        ],
                    ],
                    'tablet' => [
                        '<all_locales>' => [
                            'EUR' => '17.00',
                        ],
                    ],
                ],
                'a_simple_select-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'optionB',
                    ],
                ],
                'a_text-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'this is a text',
                    ],
                ],
                '123-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'a text for an attribute with numerical code',
                    ],
                ],
                'a_text_area-textarea' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no-boolean' => [
                    '<all_channels>' => [
                        '<all_locales>' => true,
                    ],
                ],
                'an_image-media' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'extension' => 'jpg',
                            'hash' => 'a9453e6ce89dbfd776ecae609e1c23e9cfad8277',
                            'key' => '3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg',
                            'mime_type' => 'image/jpeg',
                            'original_filename' => 'imageA.jpg',
                            'size' => 1048576,
                            'storage' => 'catalogStorage',

                        ],
                    ],
                ],
                'sku-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'foo'
                    ]
                ]
            ],
            'ancestors' => [
                'ids' => [],
                'codes' => [],
                'labels' => [],
            ],
            'label' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ]
            ],
            'document_type' => ProductInterface::class,
            'attributes_of_ancestors' => [],
            'attributes_for_this_level' => [
                'a_date',
                'a_file',
                'a_localizable_image',
                'a_localized_and_scopable_text_area',
                'a_metric',
                'a_metric_negative',
                'a_metric_without_decimal',
                'a_metric_without_decimal_negative',
                'a_multi_select',
                'a_number_float',
                'a_number_float_negative',
                'a_number_integer',
                'a_number_integer_negative',
                'a_price',
                'a_price_without_decimal',
                'a_ref_data_multi_select',
                'a_ref_data_simple_select',
                'a_scopable_price',
                'a_simple_select',
                'a_text',
                'a_text_area',
                'a_yes_no',
                'an_image',
                'sku',
                123,
            ],
        ];

        $this->assertProductIndexingFormat('foo', $expected);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    /**
     * @param string $identifier
     * @param array  $expected
     */
    private function assertProductIndexingFormat($identifier, array $expected)
    {
        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $product = $query->fromProductIdentifier($identifier);

        $this->assertIndexingFormat($product, $expected);
    }

    /**
     * @param mixed $entity
     * @param array $expected
     */
    private function assertIndexingFormat(ElasticsearchProductProjection $productProjection, array $expected)
    {
        $normalizedProductProjection = $productProjection->toArray();
        NormalizedProductCleaner::clean($normalizedProductProjection);
        NormalizedProductCleaner::clean($expected);

        $this->assertEquals($expected, $normalizedProductProjection);
    }
}
