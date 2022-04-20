<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\AssertionFailedError;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpdatedProductIdsQueryIntegration extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_all_updated_product_ids()
    {
        /** @var GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery */
        $getUpdatedProductIdsQuery = $this->get('akeneo.pim.automation.data_quality_insights.elasticsearch.get_updated_product_ids_query');

        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $this->assertEquals([], iterator_to_array($getUpdatedProductIdsQuery->since($today, 2)));

        $this->givenAnUpdatedProduct($today);
        $expectedProduct1 = $this->givenAnUpdatedProduct($today->modify('+1 SECOND'));
        $expectedProduct2 = $this->givenAnUpdatedProduct($today->modify('+1 HOUR'));

        $this->givenAProductModel('a_product_model_not_recently_updated', 'familyVariantA2', $today->modify('-1 DAY'));
        $expectedProductVariant1 = $this->givenAnUpdatedProductVariant('a_product_model_not_recently_updated', $today->modify('+1 MINUTE'));
        $this->givenAnUpdatedProductVariant('a_product_model_not_recently_updated', $today->modify('-2 MINUTE'));

        $this->givenAProductModel('a_product_model_recently_updated', 'familyVariantA2', $today->modify('+2 MINUTE'));
        $expectedProductVariant2 = $this->givenAnUpdatedProductVariant('a_product_model_recently_updated', $today->modify('-1 MINUTE'));

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productIds = iterator_to_array($getUpdatedProductIdsQuery->since($today->modify('+1 HOUR'), 3));
        $productIds = array_map(fn (ProductUuidCollection $collection) => $collection->toArray(), $productIds);

        $this->assertCount(2, $productIds);
        $this->assertCount(3, $productIds[0]);
        $this->assertCount(1, $productIds[1]);
        $productIds = array_merge($productIds[0], $productIds[1]);

        $this->assertExpectedProductId($expectedProduct1, $productIds);
        $this->assertExpectedProductId($expectedProduct2, $productIds);
        $this->assertExpectedProductId($expectedProductVariant1, $productIds);
        $this->assertExpectedProductId($expectedProductVariant2, $productIds);
    }

    public function test_it_returns_all_updated_product_model_ids()
    {
        /** @var GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery */
        $getUpdatedProductModelIdsQuery = $this->get('akeneo.pim.automation.data_quality_insights.elasticsearch.get_updated_product_model_ids_query');

        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $this->assertEquals([], iterator_to_array($getUpdatedProductModelIdsQuery->since($today, 2)));

        $this->givenAnUpdatedParentProductModel('a_product_model_not_recently_updated', $today->modify('-1 SECOND'));

        $expectedProductModel1 = $this->givenAnUpdatedParentProductModel('a_product_model_recently_updated', $today->modify('+1 SECOND'));
        $expectedProductModel2 = $this->givenAnUpdatedParentProductModel('another_product_model_recently_updated', $today->modify('+2 MINUTE'));

        $expectedSubProductModel1 = $this->givenAnUpdatedSubProductModel('a_product_model_recently_updated', $today->modify('-10 SECOND'));
        $expectedSubProductModel2 = $this->givenAnUpdatedSubProductModel('a_product_model_not_recently_updated', $today->modify('+10 SECOND'));

        $this->givenAnUpdatedProduct($today->modify('+1 MINUTE'));
        $this->givenAnUpdatedProductVariant('a_product_model_not_recently_updated', $today->modify('+1 HOUR'));

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productIds = iterator_to_array($getUpdatedProductModelIdsQuery->since($today->modify('+1 HOUR'), 3));
        $productIds = array_map(fn (ProductModelIdCollection $collection) => $collection->toArray(), $productIds);

        $this->assertCount(2, $productIds);
        $this->assertCount(3, $productIds[0]);
        $this->assertCount(1, $productIds[1]);
        $productIds = array_merge($productIds[0], $productIds[1]);

        $this->assertExpectedProductId($expectedProductModel1, $productIds);
        $this->assertExpectedProductId($expectedProductModel2, $productIds);
        $this->assertExpectedProductId($expectedSubProductModel1, $productIds);
        $this->assertExpectedProductId($expectedSubProductModel2, $productIds);
    }

    private function createProduct(): ProductInterface
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function createProductVariant(string $parentCode): ProductInterface
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->withFamily('familyA')
            ->build();

        $this->get('pim_catalog.updater.product')->update($product, ['parent' => $parentCode]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function updateProductAt(ProductInterface $product, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated WHERE id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_id' => $product->getId(),
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier($product->getIdentifier());
    }

    private function updateProductModelAt(string $productModelCode, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :updated WHERE code = :code;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'code' => $productModelCode,
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexFromProductModelCode($productModelCode);
    }

    private function givenAnUpdatedProduct(\DateTimeImmutable $updatedAt): ProductUuid
    {
        $product = $this->createProduct();
        $this->updateProductAt($product, $updatedAt);

        return new ProductUuid($product->getUuid());
    }

    private function givenAnUpdatedProductVariant(string $parentCode, \DateTimeImmutable $updatedAt): ProductUuid
    {
        $productVariant = $this->createProductVariant($parentCode);
        $this->updateProductAt($productVariant, $updatedAt);

        return new ProductUuid($productVariant->getUuid());
    }

    private function givenAProductModel(string $productModelCode, string $familyVariant, \DateTimeImmutable $updatedAt)
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->updateProductModelAt($productModelCode, $updatedAt);
    }

    private function givenAnUpdatedParentProductModel(string $productModelCode, \DateTimeImmutable $updatedAt): ProductModelId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->updateProductModelAt($productModelCode, $updatedAt);

        return new ProductModelId($productModel->getId());
    }

    private function givenAnUpdatedSubProductModel(string $parentCode, \DateTimeImmutable $updatedAt): ProductModelId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant('familyVariantA1')
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->updateProductModelAt($productModel->getCode(), $updatedAt);

        return new ProductModelId($productModel->getId());
    }

    private function assertExpectedProductId(ProductUuid $expectedProductId, array $productIds): void
    {
        foreach ($productIds as $productId) {
            if ($productId->toInt() === $expectedProductId->toInt()) {
                return;
            }
        }

        throw new AssertionFailedError(sprintf('Expected product id %d not found', $expectedProductId->toInt()));
    }
}
