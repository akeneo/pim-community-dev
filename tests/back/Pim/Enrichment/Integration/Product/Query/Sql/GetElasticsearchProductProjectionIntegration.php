<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
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

    public function test_it_gets_product_projection_of_a_variant_product()
    {
        $this->createVariantProduct();
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
            'attributes_for_this_level' => ['a_text_area', 'a_yes_no', 'sku'],
            'associations' => null,
        ];

        $this->assertProductIndexingFormat('bar', $expected);
    }

    public function test_it_gets_product_projection_of_a_product_without_family_and_without_group_and_without_values()
    {
        $this->createEmptyProduct();

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

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createEmptyProduct()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', null);
        Assert::assertCount(0, $this->get('validator')->validate($product));
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createVariantProduct()
    {
        $this->createProductModels();

        $product = $this->get('pim_catalog.builder.product')->createProduct('bar', null);
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

    private function createProductModels()
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

    private function assertProductIndexingFormat(string $identifier, array $expected)
    {

        $query = $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
        $productProjection = $query->fromProductIdentifier($identifier);

        $normalizedProductProjection = $productProjection->toArray();
        // allows to execute test from EE by removing additional properties
        $normalizedProductProjection = array_intersect_key($normalizedProductProjection, $expected);

        NormalizedProductCleaner::clean($normalizedProductProjection);
        NormalizedProductCleaner::clean($expected);
        self::sanitizeMediaAttributeData($expected);
        self::sanitizeMediaAttributeData($normalizedProductProjection);

        Assert::assertStringContainsString('product', $expected['id']);
        Assert::assertStringContainsString('product', $normalizedProductProjection['id']);

        Assert::assertSame(count($normalizedProductProjection['ancestors']['ids']), count($normalizedProductProjection['ancestors']['codes']));
        unset($expected['id'], $normalizedProductProjection['id'], $normalizedProductProjection['ancestors']['ids'], $expected['ancestors']['ids']);

        $this->assertEquals($expected, $normalizedProductProjection);
    }

    /**
     * @param array $data
     *
     * @return array
     */
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
}
