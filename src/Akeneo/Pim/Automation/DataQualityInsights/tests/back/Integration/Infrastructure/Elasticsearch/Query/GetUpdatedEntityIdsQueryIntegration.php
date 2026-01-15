<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\AssertionFailedError;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetUpdatedEntityIdsQueryIntegration extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->createAttributeOption('a_simple_select', 'optionC', ['en_US' => 'Option C']);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_all_updated_product_ids()
    {
        /** @var GetUpdatedEntityIdsQueryInterface $getUpdatedProductIdsQuery */
        $getUpdatedProductIdsQuery = $this->get('akeneo.pim.automation.data_quality_insights.elasticsearch.get_updated_product_ids_query');

        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $this->assertEquals([], iterator_to_array($getUpdatedProductIdsQuery->since($today, 2)));

        $this->givenAnUpdatedProduct($today);
        $expectedProduct1 = $this->givenAnUpdatedProduct($today->modify('+1 SECOND'));
        $expectedProduct2 = $this->givenAnUpdatedProduct($today->modify('+1 HOUR'));

        $this->givenAProductModel('a_product_model_not_recently_updated', 'familyVariantA2', $today->modify('-1 DAY'));
        $expectedProductVariant1 = $this->givenAnUpdatedProductVariant('product_variant_1', 'a_product_model_not_recently_updated', $today->modify('+1 MINUTE'), 'optionA');
        $this->givenAnUpdatedProductVariant('product_variant_2', 'a_product_model_not_recently_updated', $today->modify('-2 MINUTE'), 'optionB');

        $this->givenAProductModel('a_product_model_recently_updated', 'familyVariantA2', $today->modify('+2 MINUTE'));
        $expectedProductVariant2 = $this->givenAnUpdatedProductVariant('product_variant_3','a_product_model_recently_updated', $today->modify('-1 MINUTE'), 'optionC');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productUuids = iterator_to_array($getUpdatedProductIdsQuery->since($today->modify('+1 HOUR'), 3));
        $productUuids = array_map(fn (ProductUuidCollection $collection) => $collection->toArray(), $productUuids);

        $this->assertCount(2, $productUuids);
        $this->assertCount(3, $productUuids[0]);
        $this->assertCount(1, $productUuids[1]);
        $productUuids = array_merge($productUuids[0], $productUuids[1]);

        $this->assertExpectedEntityId($expectedProduct1, $productUuids);
        $this->assertExpectedEntityId($expectedProduct2, $productUuids);
        $this->assertExpectedEntityId($expectedProductVariant1, $productUuids);
        $this->assertExpectedEntityId($expectedProductVariant2, $productUuids);
    }

    public function test_it_returns_all_updated_product_model_ids()
    {
        /** @var GetUpdatedEntityIdsQueryInterface $getUpdatedProductIdsQuery */
        $getUpdatedProductModelIdsQuery = $this->get('akeneo.pim.automation.data_quality_insights.elasticsearch.get_updated_product_model_ids_query');

        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $this->assertEquals([], iterator_to_array($getUpdatedProductModelIdsQuery->since($today, 2)));

        $this->givenAnUpdatedParentProductModel('a_product_model_not_recently_updated', $today->modify('-1 SECOND'));

        $expectedProductModel1 = $this->givenAnUpdatedParentProductModel('a_product_model_recently_updated', $today->modify('+1 SECOND'));
        $expectedProductModel2 = $this->givenAnUpdatedParentProductModel('another_product_model_recently_updated', $today->modify('+2 MINUTE'));

        $expectedSubProductModel1 = $this->givenAnUpdatedSubProductModel('a_product_model_recently_updated', $today->modify('-10 SECOND'));
        $expectedSubProductModel2 = $this->givenAnUpdatedSubProductModel('a_product_model_not_recently_updated', $today->modify('+10 SECOND'));

        $this->givenAnUpdatedProduct($today->modify('+1 MINUTE'));
        $this->givenAnUpdatedProductVariant('product_variant_1', 'a_product_model_not_recently_updated', $today->modify('+1 HOUR'), 'optionA');

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productIds = iterator_to_array($getUpdatedProductModelIdsQuery->since($today->modify('+1 HOUR'), 3));
        $productIds = array_map(fn (ProductModelIdCollection $collection) => $collection->toArray(), $productIds);

        $this->assertCount(2, $productIds);
        $this->assertCount(3, $productIds[0]);
        $this->assertCount(1, $productIds[1]);
        $productIds = array_merge($productIds[0], $productIds[1]);

        $this->assertExpectedEntityId($expectedProductModel1, $productIds);
        $this->assertExpectedEntityId($expectedProductModel2, $productIds);
        $this->assertExpectedEntityId($expectedSubProductModel1, $productIds);
        $this->assertExpectedEntityId($expectedSubProductModel2, $productIds);
    }

    private function createProduct(?string $identifier = null, array $userIntents = []): ProductInterface
    {
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product afterwards
        $identifier = $identifier?: strval(Uuid::uuid4());

        $this->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createWithIdentifierSystemUser(
            $identifier,
            $userIntents
        ));

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function createProductVariant(string $identifier, string $parentCode, string $variantOptionValue): ProductInterface {
        return $this->createProduct($identifier, [
            new SetFamily('familyA'),
            new ChangeParent($parentCode),
            new SetSimpleSelectValue('a_simple_select', null, null, $variantOptionValue),
            new SetBooleanValue('a_yes_no', null, null, true)
        ]);
    }

    private function updateProductAt(ProductInterface $product, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated WHERE uuid = :product_uuid;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_uuid' => $product->getUuid()->getBytes(),
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductUuids([$product->getUuid()]);
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

        return ProductUuid::fromUuid($product->getUuid());
    }

    private function givenAnUpdatedProductVariant(string $identifier, string $parentCode, \DateTimeImmutable $updatedAt, string $variantOptionValue): ProductUuid
    {
        $productVariant = $this->createProductVariant($identifier, $parentCode, $variantOptionValue);
        $this->updateProductAt($productVariant, $updatedAt);

        return ProductUuid::fromUuid($productVariant->getUuid());
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
            ->withFamilyVariant('familyVariantA2')
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

    private function assertExpectedEntityId(ProductEntityIdInterface $expectedEntityId, array $entityIds): void
    {
        foreach ($entityIds as $entityId) {
            if ((string) $entityId === (string) $expectedEntityId) {
                return;
            }
        }

        throw new AssertionFailedError(sprintf('Expected entity id %s not found', (string) $expectedEntityId));
    }

    private function createAttributeOption(string $attributeCode, string $optionCode, array $labels): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => $labels,
        ]);

        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }
}
