<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Statement;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;

/**
 * Integration tests to verify a product is well saved in database.
 */
class ProductSaverIntegration extends TestCase
{
    public function testRawValuesForProductWithAllAttributes()
    {
        $product = $this->createProduct('just-a-product-with-all-possible-values', 'familyA');
        $standardValues = $this->getStandardValuesWithAllAttributes();
        $this->updateProduct($product, $standardValues);
        $this->saveProduct($product);

        $stmt = $this->createStatement('SELECT raw_values FROM pim_catalog_product ORDER BY id DESC LIMIT 1');
        $jsonRawValues = $stmt->fetchColumn();
        $rawValues = json_decode($jsonRawValues, true);
        NormalizedProductCleaner::cleanOnlyValues($rawValues);

        $storageValues = $this->getStorageValuesWithAllAttributes();
        NormalizedProductCleaner::cleanOnlyValues($storageValues);

        $this->assertEquals($storageValues, $rawValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalSqlCatalogPath()],
            false
        );
    }

    /**
     * @param string $productIdentifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    private function createProduct($productIdentifier, $familyCode)
    {
        return $this->get('pim_catalog.builder.product')->createProduct($productIdentifier, $familyCode);
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     */
    private function updateProduct(ProductInterface $product, array $data)
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
    }

    /**
     * @param ProductInterface $product
     */
    private function saveProduct(ProductInterface $product)
    {
        $this->get('pim_catalog.saver.product')->save($product);
    }

    /**
     * @param string $sql
     *
     * @return Statement
     */
    private function createStatement($sql)
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection()->query($sql);
    }

    /**
     * @return array
     */
    private function getStandardValuesWithAllAttributes()
    {
        return [
            'values' => [
                'a_file' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt',
                    ],
                ],
                'an_image' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg',
                    ],
                ],
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
                'a_metric' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '987654321987.1234', 'unit' => 'KILOWATT'],
                    ],
                ],
                'a_metric_without_decimal' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => 98, 'unit' => 'CENTIMETER'],
                    ],
                ],
                'a_metric_without_decimal_negative' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => -20, 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_metric_negative' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['amount' => '-20.5000', 'unit' => 'CELSIUS'],
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
                    ['locale' => null, 'scope' => null, 'data' => 42]
                ],
                'a_number_integer_negative' => [
                    ['locale' => null, 'scope' => null, 'data' => -42]
                ],
                'a_price' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '45.00', 'currency' => 'USD'],
                            ['amount' => '56.53', 'currency' => 'EUR']
                        ],
                    ],
                ],
                'a_price_without_decimal' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => -45, 'currency' => 'USD'],
                            ['amount' => 56, 'currency' => 'EUR']
                        ],
                    ],
                ],
                'a_ref_data_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['fabricA', 'fabricB']]
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
                        'scope'  => null,
                        'data'   => 'this is a text',
                    ],
                ],
                '123'                                => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'a text for an attribute with numerical code',
                    ],
                ],
                'a_text_area' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'a_localizable_image' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => '7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => null,
                        'data'   => '0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg',
                    ],
                ],
                'a_scopable_price' => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => [
                            ['amount' => '15.00', 'currency' => 'EUR'],
                            ['amount' => '20.00', 'currency' => 'USD'],
                        ],
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['amount' => '17.00', 'currency' => 'EUR'],
                            ['amount' => '24.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'a text area for ecommerce in English',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'tablet',
                        'data'   => 'a text area for tablets in English'
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => 'une zone de texte pour les tablettes en français',
                    ],
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getStorageValuesWithAllAttributes()
    {
        return [
            'a_file' => [
                '<all_channels>' => [
                    '<all_locales>' => '8/b/5/c/8b5cf9bfd2e7e4725fd581e03251133ada1b2c99_fileA.txt'
                ],
            ],
            'an_image' => [
                '<all_channels>' => [
                    '<all_locales>' => '3/b/5/5/3b5548f9764c0535db2ac92f047fa448cb7cea76_imageA.jpg'
                ],
            ],
            'a_date' => [
                '<all_channels>' => [
                    '<all_locales>' => '2016-06-13T00:00:00+02:00'
                ],
            ],
            'a_metric' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount' => '987654321987.1234',
                        'unit'   => 'KILOWATT',
                        // TODO: here maybe we should have a "987654321987123.4", but the measure converter
                        // TODO: returns a double that is too big, and we didn't change that
                        // TODO: see TIP-695
                        'base_data' => 987654321987123.4,
                        'base_unit' => 'WATT',
                        'family'    => 'Power',
                    ]
                ],
            ],
            'a_metric_without_decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount'    => 98,
                        'unit'      => 'CENTIMETER',
                        'base_data' => 0.98,
                        'base_unit' => 'METER',
                        'family'    => 'Length',
                    ],
                ],
            ],
            'a_metric_without_decimal_negative' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount'    => -20,
                        'unit'      => 'CELSIUS',
                        'base_data' => 253.15,
                        'base_unit' => 'KELVIN',
                        'family'    => 'Temperature',
                    ],
                ],
            ],
            'a_metric_negative' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount'    => '-20.5000',
                        'unit'      => 'CELSIUS',
                        // TODO: here maybe we should have a string, but the measure converter returns a double,
                        // TODO: and we didn't change that
                        // TODO: see TIP-695
                        'base_data' => 252.65,
                        'base_unit' => 'KELVIN',
                        'family'    => 'Temperature',
                    ],
                ],
            ],
            'a_multi_select' => [
                '<all_channels>' => [
                    '<all_locales>' => ['optionA', 'optionB'],
                ],
            ],
            'a_number_float' => [
                '<all_channels>' => [
                    '<all_locales>' => '12.5678',
                ],
            ],
            'a_number_float_negative' => [
                '<all_channels>' => [
                    '<all_locales>' => '-99.8732',
                ],
            ],
            'a_number_integer' => [
                '<all_channels>' => [
                    '<all_locales>' => 42,
                ],
            ],
            'a_number_integer_negative' => [
                '<all_channels>' => [
                    '<all_locales>' => -42,
                ],
            ],
            'a_price' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        ['amount' => '45.00', 'currency' => 'USD'],
                        ['amount' => '56.53', 'currency' => 'EUR']
                    ],
                ],
            ],
            'a_price_without_decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        ['amount' => -45, 'currency' => 'USD'],
                        ['amount' => 56, 'currency' => 'EUR']
                    ],
                ],
            ],
            'a_ref_data_multi_select' => [
                '<all_channels>' => [
                    '<all_locales>' => ['fabricA', 'fabricB'],
                ],
            ],
            'a_ref_data_simple_select' => [
                '<all_channels>' => [
                    '<all_locales>' => 'colorB',
                ],
            ],
            'a_simple_select' => [
                '<all_channels>' => [
                    '<all_locales>' => 'optionB',
                ],
            ],
            'a_text' => [
                '<all_channels>' => [
                    '<all_locales>' => 'this is a text',
                ],
            ],
            '123'    => [
                '<all_channels>' => [
                    '<all_locales>' => 'a text for an attribute with numerical code',
                ],
            ],
            'a_text_area' => [
                '<all_channels>' => [
                    '<all_locales>' => 'this is a very very very very very long  text',
                ],
            ],
            'a_yes_no' => [
                '<all_channels>' => [
                    '<all_locales>' => true,
                ],
            ],
            'a_localizable_image' => [
                '<all_channels>' => [
                    'en_US' => '7/1/3/3/713380965740f8838834cd58505aa329fcf448a5_imageB_en_US.jpg',
                    'fr_FR' => '0/5/1/9/05198fcf21b2b0d4596459f172e2e62b1a70bfd0_imageB_fr_FR.jpg',
                ],
            ],
            'a_scopable_price' => [
                'ecommerce' => [
                    '<all_locales>' => [
                        ['amount' => '15.00', 'currency' => 'EUR'],
                        ['amount' => '20.00', 'currency' => 'USD'],
                    ],
                ],
                'tablet' => [
                    '<all_locales>' => [
                        ['amount' => '17.00', 'currency' => 'EUR'],
                        ['amount' => '24.00', 'currency' => 'USD'],
                    ],
                ],
            ],
            'a_localized_and_scopable_text_area' => [
                'ecommerce' => [
                    'en_US' => 'a text area for ecommerce in English',
                ],
                'tablet' => [
                    'en_US' => 'a text area for tablets in English',
                    'fr_FR' => 'une zone de texte pour les tablettes en français',

                ],
            ],
        ];
    }
}
