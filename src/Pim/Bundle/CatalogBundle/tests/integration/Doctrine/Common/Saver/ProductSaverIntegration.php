<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\DBAL\Connection;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Test\Integration\TestCase;

/**
 * Integration tests to verify a product is well saved in database.
 */
class ProductSaverIntegration extends TestCase
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductSaver */
    protected $productSaver;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var Connection */
    protected $db;

    public function setUp()
    {
        parent::setUp();

        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productUpdater = $this->get('pim_catalog.updater.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');

        $em = $this->get('doctrine.orm.entity_manager');
        $this->db = $em->getConnection();

        //TODO: clean that, see with Skeleton
        $path = $this->getParameter('kernel.root_dir') . '/../src/Pim/Component/Catalog/tests/integration/Normalizer/Standard/common.sql';
        $this->db->executeQuery(file_get_contents($path));
    }

    public function testRawValuesForProductWithAllAttributes()
    {
        $product = $this->productBuilder->createProduct('just-a-product-with-all-possible-values', 'familyA');
        $this->productUpdater->update($product, $this->getStandardValuesWithAllAttributes());
        $this->productSaver->save($product);

        $stmt = $this->db->query('SELECT raw_values FROM pim_catalog_product ORDER BY id DESC LIMIT 1');
        $jsonRawValues = $stmt->fetchColumn();

        $this->assertEquals(
            json_encode($this->getStorageValuesWithAllAttributes()),
            $jsonRawValues
        );
    }

    /**
     * Overwritte this method to have no catalog installed.
     * Instead, we want to use the common.sql file.
     */
    protected function getConfigurationFiles()
    {
        return [];
    }

    private function getStandardValuesWithAllAttributes()
    {
        return [
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'foo'],
                ],
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
                        'data'   => ['amount' => '987.0000', 'unit' => 'KILOWATT'],
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

    private function getStorageValuesWithAllAttributes()
    {
        return [
            'sku' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ],
            ],
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
                        'amount' => '987.0000',
                        'unit'   => 'KILOWATT',
                        // TODO: wrong but makes the test pass
                        'base_data' => null,
                        'base_unit' => null,
                        'family'    => 'Power',
                    ]
                ],
            ],
            'a_metric_without_decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount' => 98,
                        'unit'   => 'CENTIMETER',
                        // TODO: wrong but makes the test pass
                        'base_data' => null,
                        'base_unit' => null,
                        'family'    => 'Length',
                    ],
                ],
            ],
            'a_metric_without_decimal_negative' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount' => -20,
                        'unit'   => 'CELSIUS',
                        // TODO: wrong but makes the test pass
                        'base_data' => null,
                        'base_unit' => null,
                        'family'    => 'Temperature',
                    ],
                ],
            ],
            'a_metric_negative' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'amount' => '-20.5000',
                        'unit'   => 'CELSIUS',
                        // TODO: wrong but makes the test pass
                        'base_data' => null,
                        'base_unit' => null,
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
