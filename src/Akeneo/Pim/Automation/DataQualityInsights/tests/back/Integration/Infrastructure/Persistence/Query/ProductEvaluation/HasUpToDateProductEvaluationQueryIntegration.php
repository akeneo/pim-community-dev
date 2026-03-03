<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\HasUpToDateProductEvaluationQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HasUpToDateProductEvaluationQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var HasUpToDateProductEvaluationQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->query = $this->get(HasUpToDateProductEvaluationQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_true_if_a_product_has_an_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productUuid = $this->givenAProductWithAnUpToDateEvaluation($today);
        $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);

        $productHasUpToDateEvaluation = $this->query->forEntityId($productUuid);
        $this->assertTrue($productHasUpToDateEvaluation);

        $productVariantId = $this->givenAProductVariantWithAnUpToDateEvaluation($today);
        $productVariantHasUpToDateEvaluation = $this->query->forEntityId($productVariantId);
        $this->assertTrue($productVariantHasUpToDateEvaluation);
    }

    public function test_it_returns_false_if_a_product_has_outdated_evaluations()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);
        $this->givenAProductWithAnUpToDateEvaluation($today);

        $productHasUpToDateEvaluation = $this->query->forEntityId($productId);
        $this->assertFalse($productHasUpToDateEvaluation);

        $levelOneProductVariantId = $this->givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent($today);
        $levelOneProductVariantHasUpToDateEvaluation = $this->query->forEntityId($levelOneProductVariantId);
        $this->assertFalse($levelOneProductVariantHasUpToDateEvaluation);

        $levelTwoProductVariantId = $this->givenAProductVariantWithAnOutdatedEvaluationComparedToItsGrandParent($today);
        $levelTwoProductVariantHasUpToDateEvaluation = $this->query->forEntityId($levelTwoProductVariantId);
        $this->assertFalse($levelTwoProductVariantHasUpToDateEvaluation);
    }

    public function test_it_returns_the_ids_of_the_products_that_have_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $expectedProductUuidA = $this->givenAProductWithAnUpToDateEvaluation($today);
        $expectedProductUuidB = $this->givenAProductWithAnUpToDateEvaluation($today);
        $outdatedProductUuid = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);
        $outdatedProductVariantUuid = $this->givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent($today);
        $this->givenAProductWithAnUpToDateEvaluation($today);


        $productUuidsWithUpToDateEvaluation = $this->query->forEntityIdCollection(ProductUuidCollection::fromProductUuids(
            [$outdatedProductUuid, $outdatedProductVariantUuid, $expectedProductUuidA, $expectedProductUuidB]
        ));
        $this->assertEqualsCanonicalizing(
            ProductUuidCollection::fromProductUuids([$expectedProductUuidA, $expectedProductUuidB]),
            $productUuidsWithUpToDateEvaluation
        );
    }

    public function test_it_returns_null_if_no_product_has_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $outdatedProductId = $this->givenAnUpdatedProductWithAnOutdatedEvaluation($today);
        $productIdCollection = $this->get(ProductUuidFactory::class)->createCollection([(string)$outdatedProductId]);

        $this->assertNull($this->query->forEntityIdCollection($productIdCollection));
    }

    private function createOrUpdateProduct(string $identifier, array $userIntents = []): ProductInterface {
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset(); // Needed to update the product afterwards

        $this->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createWithIdentifierSystemUser(
            $identifier,
            $userIntents
        ));

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function createProduct(): ProductUuid
    {
        $product = $this->createOrUpdateProduct(strval(Uuid::uuid4()));

        return $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());
    }

    private function createVariantProduct(string $parentCode, array $userIntents = []): ProductUuid
    {
        $product = $this->createOrUpdateProduct(strval(Uuid::uuid4()), [
            new SetFamily('familyA'),
            new ChangeParent($parentCode),
            ...$userIntents
        ]);

        return $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());
    }

    private function givenAProductWithAnUpToDateEvaluation(\DateTimeImmutable $today): ProductUuid
    {
        $productUuid = $this->createProduct();
        $this->updateProductAt($productUuid, $today);
        $this->updateProductEvaluationsAt($productUuid, $today->modify('+1 SECOND'));

        return $productUuid;
    }

    private function givenAnUpdatedProductWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductUuid
    {
        $productUuid = $this->createProduct();
        $this->updateProductAt($productUuid, $updatedAt);
        $this->updateProductEvaluationsAt($productUuid, $updatedAt->modify('-1 SECOND'));

        return $productUuid;
    }

    private function givenAProductVariantWithAnUpToDateEvaluation(\DateTimeImmutable $parentUpdatedAt): ProductUuid
    {
        $this->givenAProductModel('a_product_model', 'familyVariantA2', $parentUpdatedAt);
        $productUuid = $this->createVariantProduct('a_product_model', [
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
            new SetBooleanValue('a_yes_no', null, null, true),
        ]);
        $this->updateProductAt($productUuid, $parentUpdatedAt->modify('-1 DAY'));
        $this->updateProductEvaluationsAt($productUuid, $parentUpdatedAt->modify('+1 SECOND'));

        return $productUuid;
    }

    private function givenAProductVariantWithAnOutdatedEvaluationComparedToItsParent(\DateTimeImmutable $parentUpdatedAt): ProductUuid
    {
        $this->givenAProductModel('a_product_model', 'familyVariantA2', $parentUpdatedAt);
        $productUuid = $this->createVariantProduct('a_product_model', [
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
            new SetBooleanValue('a_yes_no', null, null, true),
        ]);
        $this->updateProductAt($productUuid, $parentUpdatedAt->modify('-1 DAY'));
        $this->updateProductEvaluationsAt($productUuid, $parentUpdatedAt->modify('-1 SECOND'));

        return $productUuid;
    }

    private function givenAProductVariantWithAnOutdatedEvaluationComparedToItsGrandParent(\DateTimeImmutable $grandParentUpdatedAt): ProductUuid
    {
        $this->givenAProductModel('a_product_model_with_two_variant_levels', 'familyVariantA1', $grandParentUpdatedAt);
        $this->givenASubProductModel('a_recently_updated_sub_product_model', 'familyVariantA1', 'a_product_model_with_two_variant_levels', $grandParentUpdatedAt->modify('-2 HOUR'));

        $productUuid = $this->createVariantProduct('a_recently_updated_sub_product_model', [
            new SetBooleanValue('a_yes_no', null, null, true),
        ]);
        $this->updateProductAt($productUuid, $grandParentUpdatedAt->modify('-1 HOUR'));
        $this->updateProductEvaluationsAt($productUuid, $grandParentUpdatedAt->modify('-1 SECOND'));

        return $productUuid;
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

    private function givenASubProductModel(string $productModelCode, string $familyVariant, string $parentCode, \DateTimeImmutable $updatedAt)
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant($familyVariant)
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->updateProductModelAt($productModelCode, $updatedAt);
    }

    private function updateProductAt(ProductUuid $productUuid, \DateTimeImmutable $updatedAt): void
    {
        $query = <<<SQL
UPDATE pim_catalog_product SET updated = :updated WHERE uuid = :product_uuid;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_uuid' => $productUuid->toBytes(),
        ]);
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
    }

    private function updateProductEvaluationsAt(ProductUuid $productUuid, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation e, pim_catalog_product p
SET e.evaluated_at = :evaluated_at 
WHERE p.uuid = :product_uuid AND p.uuid = e.product_uuid;
SQL;

        $this->db->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_uuid' => $productUuid->toBytes(),
        ]);
    }
}
