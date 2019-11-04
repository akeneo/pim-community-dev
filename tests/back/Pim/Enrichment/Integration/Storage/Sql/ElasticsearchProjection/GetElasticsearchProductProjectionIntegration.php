<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ElasticsearchProjection;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\DateSanitizer;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * Integration tests to check that the projection of the product is correctly fetched from the database.
 * A test exists also in EE to check that it fetches the required properties.
 */
class GetElasticsearchProductProjectionIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_gets_product_projection_of_a_variant_product_with_two_levels()
    {
        $this->createVariantProductWithTwoLevels();
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'id' => 'product_1',
            'identifier' => 'bar',
            'created' => $date->format('c'),
            'updated' => $date->format('c'),
            'family' => [
                'code' => 'familyA',
                'labels' => [
                    'zh_CN' => null,
                    'en_US' => 'A family A',
                    'fr_FR' => 'Une famille A',
                    'de_DE' => null
                ],
            ],
            'enabled' => true,
            'categories' => ['categoryA', 'categoryA1', 'categoryA2', 'categoryB'],
            'categories_of_ancestors' => ['categoryB'],
            'groups' => ['groupA', 'groupB'],
            'in_group' => [
                'groupA' => true,
                'groupB' => true,
            ],
            'completeness' => [
                'ecommerce' => ['en_US' => 31],
                'ecommerce_china' => ['en_US' => 100, 'zh_CN' => 100],
                'tablet' => ['de_DE' => 31, 'en_US' => 31, 'fr_FR' => 31],
            ],
            'family_variant' => 'familyVariantA1',
            'parent' => 'sub_product_model',
            'values' => [
                'a_text-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'text',
                    ],
                ],
                'a_number_integer-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => 10,
                    ],
                ],
                'an_image-media' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'extension' => 'jpg',
                            'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                            'key' => 'b/6/5/3/b653a55ec542315fc29abb23b3300a5255963e14_akeneo.jpg',
                            'mime_type' => 'image/jpeg',
                            'original_filename' => 'akeneo.jpg',
                            'size' => 10584,
                            'storage' => 'catalogStorage',
                        ],
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
                        '<all_locales>' => 'bar',
                    ],
                ],
            ],
            'ancestors' => [
                'ids' => ['product_model_151', 'product_model_150'],
                'codes' => [ 'sub_product_model', 'root_product_model'],
                'labels' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar',
                    ],
                ],
            ],
            'label' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar',
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
                'an_image',
            ],
            'attributes_for_this_level' => ['a_text_area', 'a_yes_no', 'sku']
        ];

        $this->assertProductIndexingFormat('bar', $expected);
    }

    public function test_it_gets_product_projection_of_a_variant_product_with_one_level()
    {
        $this->createVariantProductWithOneLevel();
        $date = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            '2016-06-14 11:12:50',
            new \DateTimeZone('UTC')
        );

        $expected = [
            'id' => 'product_1',
            'identifier' => 'bar',
            'created' => $date->format('c'),
            'updated' => $date->format('c'),
            'family' => [
                'code' => 'familyA',
                'labels' => [
                    'zh_CN' => null,
                    'en_US' => 'A family A',
                    'fr_FR' => 'Une famille A',
                    'de_DE' => null
                ],
            ],
            'enabled' => true,
            'categories' => ['categoryA'],
            'categories_of_ancestors' => [],
            'groups' => ['groupA'],
            'in_group' => [
                'groupA' => true
            ],
            'completeness' => [
                'ecommerce' => ['en_US' => 26],
                'ecommerce_china' => ['en_US' => 100, 'zh_CN' => 100],
                'tablet' => ['de_DE' => 26, 'en_US' => 26, 'fr_FR' => 26],
            ],
            'family_variant' => 'family_variant_one_level',
            'parent' => 'root_product_model',
            'values' => [
                'a_text-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'text',
                    ],
                ],
                'a_number_integer-decimal' => [
                    '<all_channels>' => [
                        '<all_locales>' => 10,
                    ],
                ],
                'an_image-media' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'extension' => 'jpg',
                            'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                            'key' => 'b/6/5/3/b653a55ec542315fc29abb23b3300a5255963e14_akeneo.jpg',
                            'mime_type' => 'image/jpeg',
                            'original_filename' => 'akeneo.jpg',
                            'size' => 10584,
                            'storage' => 'catalogStorage',
                        ],
                    ],
                ],
                'a_simple_select-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'optionB',
                    ],
                ],
                'sku-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar',
                    ],
                ],
            ],
            'ancestors' => [
                'ids' => ['product_model_151'],
                'codes' => ['root_product_model'],
                'labels' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar',
                    ],
                ],
            ],
            'label' => [
                '<all_channels>' => [
                    '<all_locales>' => 'bar',
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
                'an_image',
                'a_text_area',
                'a_yes_no',
            ],
            'attributes_for_this_level' => ['a_simple_select', 'a_text', 'sku']
        ];

        $this->assertProductIndexingFormat('bar', $expected);
    }

    public function test_it_gets_product_projection_of_a_product_without_family_and_without_group_and_without_values()
    {
        $this->createEmptyProductWithoutFamily();

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
            'enabled' => true,
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

    public function test_it_gets_own_level_attributes_of_non_variant_product_in_a_family()
    {
        $this->createProductWithFamily();

        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $productProjection = $query->fromProductIdentifiers(['bar'])['bar'];
        $normalizedProductProjection = $productProjection->toArray();

        $expectedAttributeCodesForThisLevel = [
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
            'an_image',
            'a_text_area',
            'a_yes_no',
            'sku'
        ];

        sort($normalizedProductProjection['attributes_for_this_level']);
        sort($expectedAttributeCodesForThisLevel);

        Assert::assertCount(0, $normalizedProductProjection['attributes_of_ancestors']);
        Assert::assertSame($expectedAttributeCodesForThisLevel, $normalizedProductProjection['attributes_for_this_level']);
    }

    public function test_it_gets_product_projection_values_of_a_product()
    {
        $this->createProductWithFamilyAndValues();
        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $productProjection = $query->fromProductIdentifiers(['bar'])['bar'];
        $normalizedProductProjection = $productProjection->toArray();

        $expectedValues = [
            'values' => [
                'a_date-date' => [
                    '<all_channels>' => [
                        '<all_locales>' => '2016-06-14',
                    ],
                ],
                'a_localizable_image-media' => [
                    '<all_channels>' => [
                        'en_US' => [
                            'extension' => 'jpg',
                            'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_akeneo.jpg',
                            'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                            'mime_type' => 'image/jpeg',
                            'original_filename' => 'akeneo.jpg',
                            'size' => 10584,
                            'storage' => 'catalogStorage',
                        ],
                        'fr_FR' => [
                            'extension' => 'png',
                            'key' => '6/c/e/1/6ce16e02c08751c6d2966063460c4da035283695_ziggy.png',
                            'hash' => '6da161fe9cb463beeef71e26405ad208c7dc2af5',
                            'mime_type' => 'image/png',
                            'original_filename' => 'ziggy.png',
                            'size' => 100855,
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
                'a_price-prices' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'EUR' => '56.53',
                            'USD' => '45.00',
                        ],
                    ],
                ],
                'a_ref_data_multi_select-reference_data_options' => [
                    '<all_channels>' => [
                        '<all_locales>' => [
                            'cotton',
                            'denim',
                        ],
                    ],
                ],
                'a_ref_data_simple_select-reference_data_option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'purple-heart',
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
                        '<all_locales>' => 'optionA',
                    ],
                ],
                'a_text-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'Lorem ipsum dolor sit amet',
                    ],
                ],
                'a_yes_no-boolean' => [
                    '<all_channels>' => [
                        '<all_locales>' => false,
                    ],
                ],
                'sku-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'bar',
                    ],
                ],
            ],
        ];

        self::sanitizeMediaAttributeData($expectedValues);
        self::sanitizeData($normalizedProductProjection);

        Assert::assertArrayHasKey('values', $normalizedProductProjection);
        Assert::assertEquals($expectedValues['values'], $normalizedProductProjection['values']);
    }

    public function test_that_it_returns_latest_updated_date_of_the_product_and_ancestors_with_correct_timezone()
    {
        $this->createVariantProductWithTwoLevels();

        $sql = 'UPDATE pim_catalog_product_model SET updated=:updated_date WHERE code=:code';
        $this->getConnection()->executeQuery($sql, ['updated_date' => '2028-10-01 12:34:56', 'code' => 'root_product_model']);
        $this->getConnection()->executeQuery($sql, ['updated_date' => '2030-10-01 12:34:56', 'code' => 'sub_product_model']);

        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $productProjection = $query->fromProductIdentifiers(['bar'])['bar'];

        $this->assertEquals('2030-10-01T14:34:56+02:00', $productProjection->toArray()['updated']);
    }

    public function test_that_it_throws_an_exception_when_product_identifier_does_not_exist()
    {
        $this->expectException(ObjectNotFoundException::class);

        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $query->fromProductIdentifiers(['bar'])['bar'];
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createEmptyProductWithoutFamily()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', null);
        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProductWithFamily()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', 'familyA');
        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProductWithFamilyAndValues()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', 'familyA');

        $this->get('pim_catalog.updater.product')->update($product, [
            'values' => [
                'a_date' => [['locale' => null, 'scope' => null, 'data' => '2016-06-14 11:12:50']],
                'a_localized_and_scopable_text_area' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'a text area for ecommerce in English'],
                    ['scope' => 'tablet', 'locale' => 'en_US', 'data' => 'a text area for tablets in English'],
                    ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => 'une zone de texte pour les tablettes en français'],
                ],
                'a_metric' => [['scope' => null, 'locale' => null, 'data' => ['amount' => 987654321987.1234, 'unit' => 'KILOWATT']]],
                'a_multi_select' => [['scope' => null, 'locale' => null, 'data' => ['optionA', 'optionB']]],
                'a_number_float' => [['scope' => null, 'locale' => null, 'data' => 12.5678]],
                'a_number_float_negative' => [['scope' => null, 'locale' => null, 'data' => -99.8732]],
                'a_number_integer' => [['scope' => null, 'locale' => null, 'data' => 42]],
                'a_price' => [['scope' => null, 'locale' => null, 'data' => [['currency' => 'EUR', 'amount' => 56.53],['currency' => 'USD', 'amount' => 45.00]]]],
                'a_scopable_price' => [
                    ['scope' => 'ecommerce', 'locale' => null, 'data' => [['currency' => 'USD', 'amount' => 20]]],
                    ['scope' => 'tablet', 'locale' => null, 'data' => [['currency' => 'EUR', 'amount' => 17]]],
                ],
                'a_localizable_image' => [
                    ['scope' => null, 'locale' => 'en_US', 'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))],
                    ['scope' => null, 'locale' => 'fr_FR', 'data' => $this->getFileInfoKey($this->getFixturePath('ziggy.png'))],
                ],
                'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
                'a_text' => [['scope' => null, 'locale' => null, 'data' => 'Lorem ipsum dolor sit amet']],
                'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => false]],
                'a_ref_data_multi_select' => [['scope' => null, 'locale' => null, 'data' => ['cotton', 'denim']]],
                'a_ref_data_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'purple-heart']],
            ],
        ]);

        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createVariantProductWithTwoLevels()
    {
        $this->createProductModelWithTwoLevels();

        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', 'familyA');
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['categoryA', 'categoryA1', 'categoryA2'],
            'groups' => ['groupA', 'groupB'],
            'family' => 'familyA',
            'parent' => 'sub_product_model',
            'values' => [
                'a_yes_no' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => true
                ]]
            ]
        ]);

        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createVariantProductWithOneLevel()
    {
        $this->createProductModelWithOneLevel();

        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', 'familyA');
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['categoryA'],
            'groups' => ['groupA'],
            'family' => 'familyA',
            'parent' => 'root_product_model',
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'text'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'optionB'],
                ],
            ]
        ]);

        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProductModelWithTwoLevels()
    {
        $rootProductModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($rootProductModel, [
            'code' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryB'],
            'values' => [
                'an_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => null, 'scope' => null],
                ],
                'a_number_integer' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 10],
                ],
            ]
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($rootProductModel);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.product_model')->save($rootProductModel);

        $subProductModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($subProductModel, [
            'code' => 'sub_product_model',
            'parent' => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'text'],
                ],
                'a_simple_select' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 'optionB'],
                ],
            ]
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.product_model')->save($subProductModel);
    }

    private function createProductModelWithOneLevel()
    {
        $family_variant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family_variant, [
            'code' => 'family_variant_one_level',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_simple_select'],
                    'attributes' => ['a_text'],
                ]
            ],
        ]);
        $this->assertCount(0, $this->get('validator')->validate($family_variant));
        $this->get('pim_catalog.saver.family_variant')->save($family_variant);

        $subProductModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($subProductModel, [
            'code' => 'root_product_model',
            'parent' => null,
            'family_variant' => 'family_variant_one_level',
            'values' => [
                'an_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => null, 'scope' => null],
                ],
                'a_number_integer' => [
                    ['locale' => null, 'scope'  => null, 'data'   => 10],
                ],
            ]
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($subProductModel);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.product_model')->save($subProductModel);
    }

    private function assertProductIndexingFormat(string $identifier, array $expected)
    {

        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $productProjections = $query->fromProductIdentifiers([$identifier]);
        $productProjection = $productProjections[$identifier];

        $normalizedProductProjection = $productProjection->toArray();
        // allows to execute test from EE by removing additional properties
        $normalizedProductProjection = array_intersect_key($normalizedProductProjection, $expected);

        self::sanitizeData($normalizedProductProjection);
        self::sanitizeData($expected);

        Assert::assertStringContainsString('product', $expected['id']);
        Assert::assertStringContainsString('product', $normalizedProductProjection['id']);
        Assert::assertSame(count($normalizedProductProjection['ancestors']['ids']), count($normalizedProductProjection['ancestors']['codes']));
        unset($expected['id'], $normalizedProductProjection['id'], $normalizedProductProjection['ancestors']['ids'], $expected['ancestors']['ids']);

        $this->assertEquals($expected, $normalizedProductProjection);
    }

    private static function sanitizeData(array &$productProjection): void
    {
        $productProjection['created']= DateSanitizer::sanitize($productProjection['created']);
        $productProjection['updated']= DateSanitizer::sanitize($productProjection['updated']);

        self::sanitizeMediaAttributeData($productProjection);
        sort($productProjection['categories']);
        sort($productProjection['groups']);
        ksort($productProjection['values']);
        sort($productProjection['attributes_of_ancestors']);
        sort($productProjection['attributes_for_this_level']);
    }

    private static function sanitizeMediaAttributeData(array &$projection): void
    {
        foreach ($projection['values'] as $attributeCode => $dataPerChannel) {
            foreach ($dataPerChannel as $channelCode => $dataPerlocale) {
                foreach ($dataPerlocale as $localeCode => $data) {
                    if (isset($data['key'])) {
                        $projection['values'][$attributeCode][$channelCode][$localeCode]['key'] = 'this is a media key';
                    }
                }
            }
        }
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
