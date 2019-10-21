<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemovingProductModelFromIndexIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    /** @var Client */
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');

        $this->entityBuilder->createFamilyVariant(
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
    }

    public function testRemovingUnitaryProductModel()
    {
        $products = $this->createProductModelAndChildren();
        $productModelId = $products[0]->getId();
        $subProductModelId = $products[1]->getId();
        $variantProductId = $products[2]->getId();

        $this->assertTrue($this->productModelIdIsInIndex($productModelId));
        $this->assertTrue($this->productModelIdIsInIndex($subProductModelId));
        $this->assertTrue($this->productIdIsInIndex($variantProductId));

        $this->get('pim_catalog.elasticsearch.indexer.product_model')->removeFromProductModelId($productModelId);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->assertFalse($this->productModelIdIsInIndex($productModelId));
        $this->assertFalse($this->productModelIdIsInIndex($subProductModelId));
        $this->assertFalse($this->productIdIsInIndex($variantProductId));
    }

    public function testBulkRemovingProductModel()
    {
        $products = $this->createProductModelAndChildren();
        $productModelId = $products[0]->getId();
        $subProductModelId = $products[1]->getId();
        $variantProductId = $products[2]->getId();
        $products = $this->createProductModelAndChildren();
        $productModelId2 = $products[0]->getId();
        $subProductModelId2 = $products[1]->getId();
        $variantProductId2 = $products[2]->getId();

        $this->assertTrue($this->productModelIdIsInIndex($productModelId));
        $this->assertTrue($this->productModelIdIsInIndex($subProductModelId));
        $this->assertTrue($this->productIdIsInIndex($variantProductId));
        $this->assertTrue($this->productModelIdIsInIndex($productModelId2));
        $this->assertTrue($this->productModelIdIsInIndex($subProductModelId2));
        $this->assertTrue($this->productIdIsInIndex($variantProductId2));

        $this->get('pim_catalog.elasticsearch.indexer.product_model')->removeFromProductModelIds(
            [$productModelId, $productModelId2]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->assertFalse($this->productModelIdIsInIndex($productModelId));
        $this->assertFalse($this->productModelIdIsInIndex($subProductModelId));
        $this->assertFalse($this->productIdIsInIndex($variantProductId));
        $this->assertFalse($this->productModelIdIsInIndex($productModelId2));
        $this->assertFalse($this->productModelIdIsInIndex($subProductModelId2));
        $this->assertFalse($this->productIdIsInIndex($variantProductId2));
    }

    private function productIdIsInIndex(int $productId): bool
    {
        try {
            $this->esProductAndProductModelClient->get('product_' . $productId);
        } catch (Missing404Exception $e) {
            return false;
        }

        return true;
    }

    private function productModelIdIsInIndex(int $productModelId): bool
    {
        try {
            $this->esProductAndProductModelClient->get('product_model_' . $productModelId);
        } catch (Missing404Exception $e) {
            return false;
        }

        return true;
    }

    private function createProductModelAndChildren(): array
    {
        $rootProductModel = $this->entityBuilder->createProductModel(
            'root_product_model_two_level' . uniqid(),
            'two_level_family_variant',
            null,
            []
        );
        $subProductModel = $this->entityBuilder->createProductModel(
            'sub_product_model' . uniqid(),
            'two_level_family_variant',
            $rootProductModel,
            []
        );
        $variant = $this->entityBuilder->createVariantProduct(
            'variant_product_1' . uniqid(),
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            []
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return [$rootProductModel, $subProductModel, $variant];
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
