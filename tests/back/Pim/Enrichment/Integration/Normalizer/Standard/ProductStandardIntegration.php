<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * Integration tests to verify data from database are well formatted in the standard format
 */
class ProductStandardIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    public function testEmptyDisabledProduct()
    {
        $expected = [
            'uuid' => 'bb2cd2b4-05c1-4b02-b97d-e5ef7b4312af',
            'identifier' => 'bar',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => false,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'bar',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];

        $this->assertStandardFormat('bar', $expected);
    }

    public function testEmptyEnabledProduct()
    {
        $expected = [
            'uuid' => 'b110e90c-c1c5-476b-9717-1a87fad21405',
            'identifier' => 'baz',
            'family' => null,
            'parent' => null,
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'baz',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];

        $this->assertStandardFormat('baz', $expected);
    }

    public function testProductWithAllAttributes()
    {
        $expected =
            [
                'uuid' => '0e0304dc-d7f7-4dc4-89bb-a388e1fa2bcd',
                'identifier' => 'foo',
                'family' => 'familyA',
                'parent' => null,
                'groups' => ['groupA', 'groupB'],
                'categories' => ['categoryA1', 'categoryB'],
                'enabled' => true,
                'values' => [
                    'sku' => [
                        ['locale' => null, 'scope' => null, 'data' => 'foo'],
                    ],
                    'a_file' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
                        ],
                    ],
                    'an_image' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
                        ],
                    ],
                    'a_date' => [
                        ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                    ],
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                        ],
                    ],
                    'a_metric_without_decimal' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => 98, 'unit' => 'CENTIMETER'],
                        ],
                    ],
                    'a_metric_without_decimal_negative' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => -20, 'unit' => 'CELSIUS'],
                        ],
                    ],
                    'a_metric_negative' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
                        ],
                    ],
                    'a_multi_select' => [
                        ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                    ],
                    'a_number_float' => [
                        ['locale' => null, 'scope' => null, 'data' => '12.5678'],
                    ],
                    'a_number_float_negative' => [
                        ['locale' => null, 'scope' => null, 'data' => '-99.8732'],
                    ],
                    'a_number_integer' => [
                        ['locale' => null, 'scope' => null, 'data' => 42],
                    ],
                    'a_number_integer_negative' => [
                        ['locale' => null, 'scope' => null, 'data' => -42],
                    ],
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                ['amount' => '56.53', 'currency' => 'EUR'],
                                ['amount' => '45.00', 'currency' => 'USD'],
                            ],
                        ],
                    ],
                    'a_price_without_decimal' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                ['amount' => 56, 'currency' => 'EUR'],
                                ['amount' => -45, 'currency' => 'USD'],
                            ],
                        ],
                    ],
                    'a_ref_data_multi_select' => [
                        ['locale' => null, 'scope' => null, 'data' => ['fabricA', 'fabricB']],
                    ],
                    'a_ref_data_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'colorB'],
                    ],
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'this is a text',
                        ],
                    ],
                    '123' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'a text for an attribute with numerical code',
                        ],
                    ],
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'this is a very very very very very long  text',
                        ],
                    ],
                    'a_yes_no' => [
                        ['locale' => null, 'scope' => null, 'data' => true],
                    ],
                    'a_localizable_image' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope' => null,
                            'data' => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
                        ],
                    ],
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope' => 'ecommerce',
                            'data' => [
                                ['amount' => '20.00', 'currency' => 'USD'],
                            ],
                        ],
                        [
                            'locale' => null,
                            'scope' => 'tablet',
                            'data' => [
                                ['amount' => '17.00', 'currency' => 'EUR'],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => 'a text area for ecommerce in English',
                        ],
                        [
                            'locale' => 'en_US',
                            'scope' => 'tablet',
                            'data' => 'a text area for tablets in English',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope' => 'tablet',
                            'data' => 'une zone de texte pour les tablettes en français',
                        ],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [
                    'PACK' => [
                        'groups' => [],
                        'product_uuids' => [
                            $this->getProductUuid('bar')->toString(),
                            $this->getProductUuid('baz')->toString(),
                        ],
                        'product_models' => [],
                    ],
                    'SUBSTITUTION' => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                    'UPSELL' => ['groups' => ['groupA'], 'product_uuids' => [], 'product_models' => []],
                    'X_SELL' => [
                        'groups' => ['groupB'],
                        'product_uuids' => [
                            $this->getProductUuid('bar')->toString(),
                        ],
                        'product_models' => [],
                    ],
                ],
                'quantified_associations' => [
                    "PRODUCT_SET" => [
                        "products" => [
                            ['identifier' => 'bar', "uuid" => $this->getProductUuid('bar')->toString(), "quantity" => 3],
                        ],
                        "product_models" => [
                            ["identifier" => 'baz', "quantity" => 2],
                        ],
                    ],
                ],
            ];

        $this->assertStandardFormat('foo', $expected);
    }

    public function testProductWithLongAttributesNumbers()
    {
        $uuid = $this->getProductUuid('foo');
        $sql = <<<SQL
UPDATE pim_catalog_product SET raw_values = '{\"sku\": {\"<all_channels>\": {\"<all_locales>\": \"foo\"}},\"a_file\":{\"<all_channels>\":{\"<all_locales>\":\"8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt\"}},\"an_image\":{\"<all_channels>\":{\"<all_locales>\":\"3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg\"}},\"a_date\":{\"<all_channels>\":{\"<all_locales>\":\"2016-06-13T00:00:00+02:00\"}},\"a_metric\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"987654321987.1234\",\"unit\":\"KILOWATT\",\"base_data\":\"987654321987123.4000\",\"base_unit\":\"WATT\",\"family\":\"Power\"}}},\"a_metric_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":98,\"unit\":\"CENTIMETER\",\"base_data\":\"0.98\",\"base_unit\":\"METER\",\"family\":\"Length\"}}},\"a_metric_without_decimal_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":-20,\"unit\":\"CELSIUS\",\"base_data\":\"253.150000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_metric_negative\":{\"<all_channels>\":{\"<all_locales>\":{\"amount\":\"-20.5000\",\"unit\":\"CELSIUS\",\"base_data\":\"252.650000000000\",\"base_unit\":\"KELVIN\",\"family\":\"Temperature\"}}},\"a_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"optionA\",\"optionB\"]}},\"a_number_float\":{\"<all_channels>\":{\"<all_locales>\":\"12.56781111111\"}},\"a_number_float_negative\":{\"<all_channels>\":{\"<all_locales>\":\"-99.87321111111\"}},\"a_number_integer\":{\"<all_channels>\":{\"<all_locales>\":42}},\"a_number_integer_negative\":{\"<all_channels>\":{\"<all_locales>\":-42}},\"a_price\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":\"45.00\",\"currency\":\"USD\"},{\"amount\":\"56.53\",\"currency\":\"EUR\"}]}},\"a_price_without_decimal\":{\"<all_channels>\":{\"<all_locales>\":[{\"amount\":-45,\"currency\":\"USD\"},{\"amount\":56,\"currency\":\"EUR\"}]}},\"a_ref_data_multi_select\":{\"<all_channels>\":{\"<all_locales>\":[\"fabricA\",\"fabricB\"]}},\"a_ref_data_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"colorB\"}},\"a_simple_select\":{\"<all_channels>\":{\"<all_locales>\":\"optionB\"}},\"a_text\":{\"<all_channels>\":{\"<all_locales>\":\"this is a text\"}},\"123\":{\"<all_channels>\":{\"<all_locales>\":\"a text for an attribute with numerical code\"}},\"a_text_area\":{\"<all_channels>\":{\"<all_locales>\":\"this is a very very very very very long  text\"}},\"a_yes_no\":{\"<all_channels>\":{\"<all_locales>\":true}},\"a_localizable_image\":{\"<all_channels>\":{\"en_US\":\"7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg\",\"fr_FR\":\"0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg\"}},\"a_scopable_price\":{\"ecommerce\":{\"<all_locales>\":[{\"amount\":\"15.00\",\"currency\":\"EUR\"},{\"amount\":\"20.00\",\"currency\":\"USD\"}]},\"tablet\":{\"<all_locales>\":[{\"amount\":\"17.00\",\"currency\":\"EUR\"},{\"amount\":\"24.00\",\"currency\":\"USD\"}]}},\"a_localized_and_scopable_text_area\":{\"ecommerce\":{\"en_US\":\"a text area for ecommerce in English\"},\"tablet\":{\"en_US\":\"a text area for tablets in English\",\"fr_FR\":\"une zone de texte pour les tablettes en français\"}}}'
WHERE uuid = :uuid ;
SQL;
        $this->get('database_connection')->executeQuery($sql, ['uuid' => $uuid->getBytes()]);

        $expected =
            [
                'uuid' => '0e0304dc-d7f7-4dc4-89bb-a388e1fa2bcd',
                'identifier' => 'foo',
                'family' => 'familyA',
                'parent' => null,
                'groups' => ['groupA', 'groupB'],
                'categories' => ['categoryA1', 'categoryB'],
                'enabled' => true,
                'values' => [
                    'sku' => [
                        ['locale' => null, 'scope' => null, 'data' => 'foo'],
                    ],
                    'a_file' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
                        ],
                    ],
                    'an_image' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
                        ],
                    ],
                    'a_date' => [
                        ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                    ],
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                        ],
                    ],
                    'a_metric_without_decimal' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => 98, 'unit' => 'CENTIMETER'],
                        ],
                    ],
                    'a_metric_without_decimal_negative' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => -20, 'unit' => 'CELSIUS'],
                        ],
                    ],
                    'a_metric_negative' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
                        ],
                    ],
                    'a_multi_select' => [
                        ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                    ],
                    'a_number_float' => [
                        ['locale' => null, 'scope' => null, 'data' => '12.56781111111'],
                    ],
                    'a_number_float_negative' => [
                        ['locale' => null, 'scope' => null, 'data' => '-99.87321111111'],
                    ],
                    'a_number_integer' => [
                        ['locale' => null, 'scope' => null, 'data' => 42],
                    ],
                    'a_number_integer_negative' => [
                        ['locale' => null, 'scope' => null, 'data' => -42],
                    ],
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                ['amount' => '56.53', 'currency' => 'EUR'],
                                ['amount' => '45.00', 'currency' => 'USD'],
                            ],
                        ],
                    ],
                    'a_price_without_decimal' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                ['amount' => 56, 'currency' => 'EUR'],
                                ['amount' => -45, 'currency' => 'USD'],
                            ],
                        ],
                    ],
                    'a_ref_data_multi_select' => [
                        ['locale' => null, 'scope' => null, 'data' => ['fabricA', 'fabricB']],
                    ],
                    'a_ref_data_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'colorB'],
                    ],
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'this is a text',
                        ],
                    ],
                    '123' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'a text for an attribute with numerical code',
                        ],
                    ],
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'this is a very very very very very long  text',
                        ],
                    ],
                    'a_yes_no' => [
                        ['locale' => null, 'scope' => null, 'data' => true],
                    ],
                    'a_localizable_image' => [
                        [
                            'locale' => 'en_US',
                            'scope' => null,
                            'data' => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope' => null,
                            'data' => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
                        ],
                    ],
                    'a_scopable_price' => [
                        [
                            'locale' => null,
                            'scope' => 'ecommerce',
                            'data' => [
                                ['amount' => '20.00', 'currency' => 'USD'],
                            ],
                        ],
                        [
                            'locale' => null,
                            'scope' => 'tablet',
                            'data' => [
                                ['amount' => '17.00', 'currency' => 'EUR'],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => 'a text area for ecommerce in English',
                        ],
                        [
                            'locale' => 'en_US',
                            'scope' => 'tablet',
                            'data' => 'a text area for tablets in English',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope' => 'tablet',
                            'data' => 'une zone de texte pour les tablettes en français',
                        ],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [
                    'PACK' => [
                        'groups' => [],
                        'product_uuids' => [
                            $this->getProductUuid('bar')->toString(),
                            $this->getProductUuid('baz')->toString(),
                        ],
                        'product_models' => [],
                    ],
                    'SUBSTITUTION' => ['groups' => [], 'product_uuids' => [], 'product_models' => []],
                    'UPSELL' => ['groups' => ['groupA'], 'product_uuids' => [], 'product_models' => []],
                    'X_SELL' => [
                        'groups' => ['groupB'],
                        'product_uuids' => [
                            $this->getProductUuid('bar')->toString(),
                        ],
                        'product_models' => [],
                    ],
                ],
                'quantified_associations' => [
                    "PRODUCT_SET" => [
                        "products" => [
                            ['identifier' => 'bar', "uuid" => $this->getProductUuid('bar')->toString(), "quantity" => 3],
                        ],
                        "product_models" => [
                            ["identifier" => 'baz', "quantity" => 2],
                        ],
                    ],
                ],
            ];

        $this->assertStandardFormat('foo', $expected);
    }

    /**
     * @param string $identifier
     * @param array  $expected
     */
    private function assertStandardFormat($identifier, array $expected): void
    {
        $repository = $this->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier($identifier);

        $result = $this->normalizeProductToStandardFormat($product);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function normalizeProductToStandardFormat(ProductInterface $product): array
    {
        $serializer = $this->get('pim_standard_format_serializer');

        return $serializer->normalize($product, 'standard');
    }
}
