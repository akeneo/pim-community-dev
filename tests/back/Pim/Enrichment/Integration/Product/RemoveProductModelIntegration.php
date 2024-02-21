<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * @author    Florian Klein (florian.klein@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveProductModelIntegration extends TestCase
{
    /**
     * @test
     */
    public function removing_a_product_model_deletes_its_children_too()
    {
        $this->arrange();

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productModelRemover = $this->get('pim_catalog.remover.product_model');
        $productModelRepository = $this->get('pim_catalog.repository.product_model');
        $productRepository = $this->get('pim_catalog.repository.product');

        $this->assertTrue($this->productIdentifierIsInIndex('root_product_model_two_level'));
        $this->assertTrue($this->productIdentifierIsInIndex('sub_product_model'));
        $this->assertTrue($this->productIdentifierIsInIndex('variant_product_1'));

        $rootProductModel = $productModelRepository->findOneByIdentifier('root_product_model_two_level');
        $productModelRemover->remove($rootProductModel);

        $this->assertNull($productModelRepository->findOneByIdentifier('root_product_model_two_level'));
        $this->assertNull($productModelRepository->findOneByIdentifier('sub_product_model'));
        $this->assertNull($productRepository->findOneByIdentifier('variant_product_1'));

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->assertFalse($this->productIdentifierIsInIndex('root_product_model_two_level'));
        $this->assertFalse($this->productIdentifierIsInIndex('sub_product_model'));
        $this->assertFalse($this->productIdentifierIsInIndex('variant_product_1'));
    }

    /**
     * Inserts and returns a product model hierarchy of 1 root, 1 sub-model and 1 variant
     */
    private function arrange(): array
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createFamilyVariant(
            [
                'code' => 'two_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                    ],
                ],
            ]
        );

        $rootProductModel = $entityBuilder->createProductModel(
            'root_product_model_two_level',
            'two_level_family_variant',
            null,
            []
        );

        $subProductModel = $entityBuilder->createProductModel(
            'sub_product_model',
            'two_level_family_variant',
            $rootProductModel,
            []
        );

        $variant = $entityBuilder->createVariantProduct(
            'variant_product_1',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            []
        );

        return [$rootProductModel, $subProductModel, $variant];
    }

    private function productIdentifierIsInIndex(string $identifier): bool
    {
        $res = $this->get('akeneo_elasticsearch.client.product_and_product_model')->search(
            ['query' => ['term' => ['identifier' => $identifier]]]
        );

        return $res['hits']['total']['value'] > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
