<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsHandler;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsHandlerIntegration extends DataQualityInsightsTestCase
{
    private Connection $dbConnection;
    private Clock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->clock = $this->get(SystemClock::class);

        $this->createAttribute('name');
        $this->createSimpleSelectAttributeWithOptions('color', ['red', 'blue']);
        $this->createFamily('shoes', ['attributes' => ['sku', 'name', 'color']]);
        $this->createFamilyVariant('shoes_color', 'shoes', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => [],
                ],
            ],
        ]);

        $this->deleteAllProductModelCriterionEvaluations();
    }

    public function test_it_launches_product_and_product_model_evaluations(): void
    {
        $productToEvaluate1 = $this->createProduct('product_to_evaluate_1', ['family' => 'shoes']);
        $productToEvaluate2 = $this->createProduct('product_to_evaluate_2', ['family' => 'shoes']);
        $whateverProduct = $this->createProduct('whatever_product', ['family' => 'shoes']);

        $productToEvaluateUuid1 = ProductUuid::fromUuid($productToEvaluate1->getUuid());
        $productToEvaluateUuid2 = ProductUuid::fromUuid($productToEvaluate2->getUuid());
        $whateverProductUuid = ProductUuid::fromUuid($whateverProduct->getUuid());

        $productModelToEvaluate1 = $this->createProductModel('product_model_to_evaluate_1', 'shoes_color');
        $productModelToEvaluate2 = $this->createProductModel('product_model_to_evaluate_2', 'shoes_color');
        $whateverProductModel = $this->createProductModel('whatever_product_model', 'shoes_color');

        $productModelToEvaluateId1 = new ProductModelId($productModelToEvaluate1->getId());
        $productModelToEvaluateId2 = new ProductModelId($productModelToEvaluate2->getId());
        $whateverProductModelId = new ProductModelId($whateverProductModel->getId());

        $this->assertProductsAreNotEvaluated(ProductUuidCollection::fromProductUuids([$productToEvaluateUuid1, $productToEvaluateUuid2, $whateverProductUuid]));
        $this->assertProductScoreIsNotComputed($productToEvaluateUuid1);
        $this->assertProductScoreIsNotComputed($productToEvaluateUuid2);
        $this->assertProductScoreIsNotComputed($whateverProductUuid);
        $this->assertProductModelsAreNotEvaluated(ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId1, $productModelToEvaluateId2, $whateverProductModelId]));
        $this->assertProductModelScoreIsNotComputed($productModelToEvaluateId1);
        $this->assertProductModelScoreIsNotComputed($productModelToEvaluateId2);
        $this->assertProductModelScoreIsNotComputed($whateverProductModelId);

        $message = new LaunchProductAndProductModelEvaluationsMessage(
            $this->clock->fromString('2023-03-16 14:46:32'),
            ProductUuidCollection::fromProductUuids([$productToEvaluateUuid1, $productToEvaluateUuid2]),
            ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId1, $productModelToEvaluateId2]),
            []
        );

        ($this->get(LaunchProductAndProductModelEvaluationsHandler::class))($message);

        $this->assertProductsAreEvaluated($message->productUuids);
        $this->assertProductScoreIsComputed($productToEvaluateUuid1);
        $this->assertProductScoreIsComputed($productToEvaluateUuid2);
        $this->assertProductScoreIsNotComputed($whateverProductUuid);
        $this->assertProductModelsAreEvaluated($message->productModelIds);
        $this->assertProductModelScoreIsComputed($productModelToEvaluateId1);
        $this->assertProductModelScoreIsComputed($productModelToEvaluateId2);
        $this->assertProductModelScoreIsNotComputed($whateverProductModelId);
    }

    public function test_it_does_not_trigger_error_when_product_or_product_model_does_not_exist_anymore(): void
    {
        $productToEvaluate1 = $this->createProduct('product_to_evaluate_1', ['family' => 'shoes']);

        $productToEvaluateUuid = ProductUuid::fromUuid($productToEvaluate1->getUuid());
        $productThatNotExist = ProductUuid::fromUuid(Uuid::uuid4());

        $productModelToEvaluate = $this->createProductModel('product_model_to_evaluate_1', 'shoes_color');

        $productModelToEvaluateId = new ProductModelId($productModelToEvaluate->getId());
        // A non-existing id can be simply defined from the last created product-model
        $productModelThatNotExistId = new ProductModelId($productModelToEvaluate->getId() + 42);

        $this->assertProductsAreNotEvaluated(ProductUuidCollection::fromProductUuids([$productToEvaluateUuid, $productThatNotExist]));
        $this->assertProductScoreIsNotComputed($productToEvaluateUuid);
        $this->assertProductScoreIsNotComputed($productThatNotExist);
        $this->assertProductModelsAreNotEvaluated(ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId, $productModelThatNotExistId]));
        $this->assertProductModelScoreIsNotComputed($productModelToEvaluateId);
        $this->assertProductModelScoreIsNotComputed($productModelThatNotExistId);

        $message = new LaunchProductAndProductModelEvaluationsMessage(
            $this->clock->fromString('2023-03-16 14:46:32'),
            ProductUuidCollection::fromProductUuids([$productToEvaluateUuid, $productThatNotExist]),
            ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId, $productModelThatNotExistId]),
            []
        );

        ($this->get(LaunchProductAndProductModelEvaluationsHandler::class))($message);

        $this->assertProductsAreEvaluated(ProductUuidCollection::fromProductUuid($productToEvaluateUuid));
        $this->assertProductScoreIsComputed($productToEvaluateUuid);
        $this->assertProductModelsAreEvaluated(ProductModelIdCollection::fromProductModelIds([$productModelToEvaluateId]));
        $this->assertProductModelScoreIsComputed($productModelToEvaluateId);
        $this->assertProductModelScoreIsNotComputed($productModelThatNotExistId);
    }

    private function assertProductsAreNotEvaluated(ProductUuidCollection $productUuids): void
    {
        $query = <<<SQL
SELECT 1
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid IN (:product_uuids) AND evaluated_at IS NOT NULL
LIMIT 1;
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['product_uuids' => $productUuids->toArrayBytes()],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();

        Assert::assertFalse($result, 'Some products are evaluated');
    }

    private function assertProductModelsAreNotEvaluated(ProductModelIdCollection $productModelIds): void
    {
        $query = <<<SQL
SELECT 1
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_model_ids) AND evaluated_at IS NOT NULL
LIMIT 1;
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['product_model_ids' => $productModelIds->toArrayString()],
            ['product_model_ids' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();

        Assert::assertFalse($result, 'Some product models are evaluated');
    }

    private function assertProductModelsAreEvaluated(ProductModelIdCollection $productModelIds): void
    {
        $query = <<<SQL
SELECT product_id, COUNT(*) AS nb_evaluated_criteria
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_model_ids) AND evaluated_at IS NOT NULL
GROUP BY product_id;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_model_ids' => $productModelIds->toArrayString()],
            ['product_model_ids' => Connection::PARAM_STR_ARRAY]
        );

        $nbEvaluationsByProductModel = [];
        while ($row = $stmt->fetchAssociative()) {
            $nbEvaluationsByProductModel[$row['product_id']] = (int) $row['nb_evaluated_criteria'];
        }

        $nbCriteria = \count($this->get('akeneo.pim.automation.data_quality_insights.product_criteria_by_feature_registry')->getAllCriterionCodes());

        /** @var ProductModelId $productUuid */
        foreach ($productModelIds as $productModelId) {
            Assert::assertArrayHasKey((string) $productModelId, $nbEvaluationsByProductModel, sprintf('The product model %s should have evaluations', $productModelId));
            Assert::assertSame($nbCriteria, $nbEvaluationsByProductModel[(string) $productModelId], sprintf('All the %d criteria should be evaluated for the product model %s', $nbCriteria, $productModelId));
        }
    }

    private function assertProductsAreEvaluated(ProductUuidCollection $productUuids): void
    {
        $query = <<<SQL
SELECT BIN_TO_UUID(product_uuid) AS product_uuid, COUNT(*) AS nb_evaluated_criteria
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid IN (:product_uuids) AND evaluated_at IS NOT NULL
GROUP BY product_uuid;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['product_uuids' => $productUuids->toArrayBytes()],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        );

        $nbEvaluationsByProduct = [];
        while ($row = $stmt->fetchAssociative()) {
            $nbEvaluationsByProduct[$row['product_uuid']] = (int) $row['nb_evaluated_criteria'];
        }

        $nbCriteria = \count($this->get('akeneo.pim.automation.data_quality_insights.product_criteria_by_feature_registry')->getAllCriterionCodes());

        /** @var ProductUuid $productUuid */
        foreach ($productUuids as $productUuid) {
            Assert::assertArrayHasKey((string) $productUuid, $nbEvaluationsByProduct, sprintf('The product %s should have evaluations', $productUuid));
            Assert::assertSame($nbCriteria, $nbEvaluationsByProduct[(string) $productUuid], sprintf('All the %d criteria should be evaluated for the product %s', $nbCriteria, $productUuid));
        }
    }

    protected function assertProductScoreIsComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation does not exist. Product uuid: %s', $productUuid->__toString())
        );
    }

    protected function assertProductScoreIsNotComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation exists, it should not. Product uuid: %s', $productUuid->__toString())
        );
    }

    private function isProductScoreComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                SELECT product_uuid
                FROM pim_data_quality_insights_product_score
                WHERE product_uuid = :product_uuid AND evaluated_at = :evaluated_at
            SQL,
            ['product_uuid' => $productUuid->toBytes(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }

    protected function assertProductModelScoreIsComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation does not exist. Product uuid: %s', $productModelId->__toString())
        );
    }

    protected function assertProductModelScoreIsNotComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation exists, it should not. Product model id: %s', $productModelId->__toString())
        );
    }

    private function isProductModelScoreComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                SELECT product_model_id
                FROM pim_data_quality_insights_product_model_score
                WHERE product_model_id = :product_model_id AND evaluated_at = :evaluated_at
            SQL,
            ['product_model_id' => $productModelId->toInt(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }
}
