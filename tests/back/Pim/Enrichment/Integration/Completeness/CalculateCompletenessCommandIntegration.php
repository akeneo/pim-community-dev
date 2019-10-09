<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use PHPUnit\Framework\Assert;

class CalculateCompletenessCommandIntegration extends TestCase
{
    private $productIds = [];
    private $productModelIds = [];

    public function test_that_it_computes_completeness_and_reindexes_all_products_and_their_ancestors()
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $commandLauncher->execute('pim:completeness:calculate');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $identifiers = ['simple_product', 'variant_A_yes', 'variant_A_no'];
        $this->assertCompletenessWasComputedForProducts($identifiers);
        foreach ($identifiers as $identifier) {
            Assert::assertTrue($this->isProductIndexed($this->productIds[$identifier]));
        }
        foreach (['sub_pm_A', 'root_pm'] as $productModelCode) {
            Assert::assertTrue($this->isProductModelIndexed($this->productModelIds[$productModelCode]));
        }
        // sub_pm_B has no variant product, the command should not reindex it
        Assert::assertFalse($this->isProductModelIndexed($this->productModelIds['sub_pm_B']));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
                ],
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub_pm_B',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionB']],
                ],
            ]
        );
        $this->createProduct(
            'variant_A_yes',
            [
                'parent' => 'sub_pm_A',
                'values' => [
                    'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => true]],
                ],
            ]
        );
        $this->createProduct(
            'variant_A_no',
            [
                'parent' => 'sub_pm_A',
                'values' => [
                    'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => false]],
                ],
            ]
        );
        $this->createProduct(
            'simple_product',
            [
                'family' => 'familyA3',
                'values' => [
                    'a_yes_no' => [['scope' => null, 'locale' => null, 'data' => true]],
                ],
            ]
        );

        $this->purgeCompletenessAndResetIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function purgeCompletenessAndResetIndex(): void
    {
        $this->get('database_connection')->executeUpdate('DELETE c.* from pim_catalog_completeness c');
        $client = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $client->refreshIndex();
        $client->bulkDelete(
            array_map(
                function (int $productModelId): string {
                    return sprintf('product_model_%d', $productModelId);
                },
                $this->productModelIds
            )
        );
        $client->bulkDelete(
            array_map(
                function (int $productId): string {
                    return sprintf('product_%d', $productId);
                },
                $this->productIds
            )
        );
    }

    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->productIds[$identifier] = $product->getId();
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->productModelIds[$productModel->getCode()] = $productModel->getId();
    }

    private function assertCompletenessWasComputedForProducts(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $completenesses = $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
                ->fromProductId($this->productIds[$identifier]);
            Assert::assertCount(6, $completenesses); // 3 channels * 2 locales
        }
    }

    private function isProductIndexed(int $productId): bool
    {
        return null !== $this->get('akeneo_elasticsearch.client.product_and_product_model')
                             ->get(sprintf('product_%d', $productId));
    }

    private function isProductModelIndexed(int $productModelId): bool
    {
        try {
            $this->get('akeneo_elasticsearch.client.product_and_product_model')->get(
                sprintf('product_model_%d', $productModelId)
            );
        } catch (Missing404Exception $e) {
            return false;
        }

        return true;
    }
}
