<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\ProductModel\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class GetElasticsearchProductModelProjectionIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_that_it_gets_the_projection_of_a_sub_product_model()
    {
        $this->createRootProductModel();
        $this->createSubProductModel();

        $expected = [
            'id' => 'product_model_123',
            'identifier' => 'sub_product_model_code',
            'family' => [
                'code' => 'familyA',
                'labels' => [
                    'de_DE' => null,
                    'en_US' => 'A family A',
                    'fr_FR' => 'Une famille A',
                    'zh_CN' => null
                ]
            ],
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryA2', 'categoryA1'],
            'categories_of_ancestors' => ['categoryA1'],
            'parent' => 'root_product_model_code',
            'values' => [
                'a_simple_select-option' => ['<all_channels>' => ['<all_locales>' => 'optionB']]
            ],
            'all_complete' => [],
            'all_incomplete' => [],
            'ancestors' => [
                'codes' => ['root_product_model_code'],
                'labels' => []
            ],
            'label' => [],
            'document_type' => 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelInterface',
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
                'an_image'
            ],
            'attributes_for_this_level' => [
                'a_simple_select',
                'a_text'
            ]
        ];

        $this->checkProductModelProjectionFormat('sub_product_model_code', $expected);
    }

    public function test_that_it_gets_the_projection_of_a_root_product_model()
    {
        $this->createRootProductModel();

        $expected = [
            'identifier' => 'root_product_model_code',
            'family' => [
                'code' => 'familyA',
                'labels' => [
                    'de_DE' => null,
                    'en_US' => 'A family A',
                    'fr_FR' => 'Une famille A',
                    'zh_CN' => null
                ]
            ],
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryA1'],
            'categories_of_ancestors' => [],
            'parent' => null,
            'values' => [],
            'all_complete' => [],
            'all_incomplete' => [],
            'ancestors' => [
                'codes' => [],
                'labels' => []
            ],
            'label' => [],
            'document_type' => 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelInterface',
            'attributes_of_ancestors' => [],
            'attributes_for_this_level' => [
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
                'an_image'
            ]
        ];

        $this->checkProductModelProjectionFormat('root_product_model_code', $expected);
    }

    public function test_that_it_returns_latest_updated_date()
    {
        $this->createRootProductModel();
        $this->createSubProductModel();
        $date = new \DateTime();
        $date->modify('+1 day');

        $this->getConnection()->executeQuery(sprintf(
            'UPDATE pim_catalog_product_model SET updated="%s" WHERE code="%s"',
            $date->format('Y-m-d H:i:s'),
            'root_product_model_code'
        ));

        $this->assertEquals($this->getProductModelProjectionArray('sub_product_model_code')['updated'], $date->format('c'));
    }


    /**
     * The all_complete fields is an array of "integer" indexed by channel and locale:
     *    - 1 means that all variant product are complete
     *    - 0 means that there is at least variant product incomplete
     *
     * The all_incomplete field is an array of "integer" indexed by channel and locale:
     *    - 1 means that all variant product are incomplete
     *    - 0 means that at least one product is complete
     */
    public function test_that_it_get_completeness_of_the_product_model_with_complete_and_incomplete_variant_products()
    {
        $this->createRootProductModel();
        $this->createSubProductModel();
        $this->createProduct([
            'parent' => 'sub_product_model_code',
            'values' => [
                'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
            ],
        ]);

        $this->assertEquals($this->getProductModelProjectionArray('sub_product_model_code')['all_complete'], [
            'tablet' => [
                'de_DE' => 0,
                'en_US' => 0,
                'fr_FR' => 0,
            ],
            'ecommerce' => [
                'en_US' => 0
            ],
            'ecommerce_china' => [
                'en_US' => 1,
                'zh_CN' => 1
            ]
        ]);

        $this->assertEquals($this->getProductModelProjectionArray('sub_product_model_code')['all_incomplete'], [
            'tablet' => [
                'de_DE' => 1,
                'en_US' => 1,
                'fr_FR' => 1,
            ],
            'ecommerce' => [
                'en_US' => 1
            ],
            'ecommerce_china' => [
                'en_US' => 0,
                'zh_CN' => 0
            ]
        ]);
    }

    private function createRootProductModel()
    {
        $this->createProductModel([
            'code' => 'root_product_model_code',
            'family_variant' => 'familyVariantA1',
            'values' => [
            ],
            'categories' => ['categoryA1'],
        ]);
    }

    private function createSubProductModel()
    {
        $this->createProductModel([
            'code' => 'sub_product_model_code',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_product_model_code',
            'values' => [
                'a_simple_select' => [['locale' => null, 'scope'  => null, 'data' => 'optionB']],
            ],
            'categories' => ['categoryA2'],
        ]);
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        $this->assertCount(0, $errors);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function createProduct(array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('new_product_'.rand());
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertCount(0, $errors);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function checkProductModelProjectionFormat($code, $expected)
    {
        $normalizedProductModelProjection = $this->getProductModelProjectionArray($code);

        $this->assertRegExp('/product_model_\d+/', $normalizedProductModelProjection['id']);
        $this->assertRegexp('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}/', $normalizedProductModelProjection['created']);
        $this->assertRegexp('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}/', $normalizedProductModelProjection['updated']);
        unset($normalizedProductModelProjection['created']);
        unset($normalizedProductModelProjection['updated']);
        unset($expected['id'], $normalizedProductModelProjection['id'], $normalizedProductModelProjection['ancestors']['ids'], $expected['ancestors']['ids']);
        
        $this->assertEquals($expected, $normalizedProductModelProjection);
    }

    private function getProductModelProjectionArray($code): array
    {
        $productModelProjection = $this
            ->getGetElasticsearchProductModelProjection()
            ->fromProductModelCodes([$code])[$code];

        return $productModelProjection->toArray();
    }

    private function getGetElasticsearchProductModelProjection(): GetElasticsearchProductModelProjectionInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_model_projection');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
