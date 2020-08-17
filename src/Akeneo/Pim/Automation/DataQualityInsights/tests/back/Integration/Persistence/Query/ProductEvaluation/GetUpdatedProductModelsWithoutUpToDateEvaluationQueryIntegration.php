<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpdatedProductModelsWithoutUpToDateEvaluationQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\AssertionFailedError;
use Ramsey\Uuid\Uuid;

final class GetUpdatedProductModelsWithoutUpToDateEvaluationQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var GetUpdatedProductModelsWithoutUpToDateEvaluationQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->query = $this->get(GetUpdatedProductModelsWithoutUpToDateEvaluationQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_all_updated_product_models_id_without_up_to_date_evaluation()
    {
        $today = new \DateTimeImmutable('2020-03-02 11:34:27');
        $this->assertEquals([], iterator_to_array($this->query->execute($today, 2)));

        $expectedProductModel1 = $this->givenAProductModelWithoutAnyEvaluation();
        $expectedProductModel2 = $this->givenAnUpdatedProductModelWithAnOutdatedEvaluation($today);
        $expectedProductModel3 = $this->givenAnUpdatedSubProductModelWithAnOutdatedEvaluationComparedToItsParent($today);
        $this->givenAnUpdatedProductModelWithAnUpToDateEvaluation($today);
        $this->givenAnOldUpdatedProductModelWithAnOutdatedEvaluation($today);
        $this->givenAnUpdatedProductModelWithPendingOutdatedEvaluations($today);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productModelIds = iterator_to_array($this->query->execute($today->modify('-1 DAY'), 2));

        $this->assertCount(2, $productModelIds);
        $this->assertCount(2, $productModelIds[0]);
        $this->assertCount(1, $productModelIds[1]);
        $productModelIds = array_merge($productModelIds[0], $productModelIds[1]);

        $this->assertExpectedProductModelId($expectedProductModel1, $productModelIds);
        $this->assertExpectedProductModelId($expectedProductModel2, $productModelIds);
        $this->assertExpectedProductModelId($expectedProductModel3, $productModelIds);
    }

    private function createProductModel(string $familyVariant): ProductModelInterface
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function updateProductModelEvaluationsAt(int $productModelId, \DateTimeImmutable $evaluatedAt, string $status = CriterionEvaluationStatus::DONE): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation 
SET evaluated_at = :evaluated_at, status = :status
WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productModelId,
            'status' => $status,
        ]);
    }

    private function removeProductModelEvaluations(int $productModelId): void
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productModelId,]);
    }

    private function updateProductModelAt(ProductModelInterface $productModel, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :updated WHERE id = :product_model_id;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_model_id' => $productModel->getId(),
        ]);

        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexFromProductModelCode($productModel->getCode());
    }

    private function givenAProductModelWithoutAnyEvaluation(): ProductId
    {
        $productModel = $this->createProductModel('familyVariantA2');
        $this->removeProductModelEvaluations($productModel->getId());

        return new ProductId($productModel->getId());
    }

    private function givenAnUpdatedProductModelWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $productModel = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModel, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModel->getId(), $updatedAt->modify('-1 SECOND'));

        return new ProductId($productModel->getId());
    }

    private function givenAnUpdatedProductModelWithPendingOutdatedEvaluations(\DateTimeImmutable $updatedAt): ProductId
    {
        $productModel = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModel, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModel->getId(), $updatedAt->modify('-1 SECOND'), CriterionEvaluationStatus::PENDING);

        return new ProductId($productModel->getId());
    }

    private function givenAnUpdatedProductModelWithAnUpToDateEvaluation(\DateTimeImmutable $today)
    {
        $updatedAt = $today->modify('-2 SECOND');
        $productModel = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModel, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModel->getId(), $updatedAt->modify('+1 SECOND'));
    }

    private function givenAnOldUpdatedProductModelWithAnOutdatedEvaluation(\DateTimeImmutable $today)
    {
        $productModel = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModel, $today->modify('-2 DAY'));
        $this->createProductModelCriteriaEvaluations($productModel->getId(), $today->modify('-3 DAY'));
    }

    private function givenAParentProductModel(string $productModelCode, \DateTimeImmutable $updatedAt)
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->updateProductModelAt($productModel, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModel->getId(), $updatedAt->modify('+1 SECOND'));
    }

    private function givenAnUpdatedSubProductModelWithAnOutdatedEvaluationComparedToItsParent(\DateTimeImmutable $updatedAt): ProductId
    {
        $parentCode = strval(Uuid::uuid4());
        $this->givenAParentProductModel($parentCode, $updatedAt);

        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant('familyVariantA1')
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->updateProductModelAt($productModel, $updatedAt->modify('-1 HOUR'));
        $this->createProductModelCriteriaEvaluations($productModel->getId(), $updatedAt->modify('-1 SECOND'));

        return new ProductId($productModel->getId());
    }

    private function assertExpectedProductModelId(ProductId $expectedProductModelId, array $productModelIds): void
    {
        foreach ($productModelIds as $productModelId) {
            if ($productModelId->toInt() === $expectedProductModelId->toInt()) {
                return;
            }
        }

        throw new AssertionFailedError(sprintf('Expected product model id %d not found', $expectedProductModelId->toInt()));
    }

    private function createProductModelCriteriaEvaluations(int $productModelId, \DateTimeImmutable $createdAt, string $status = CriterionEvaluationStatus::DONE): void
    {
        $this->removeProductModelEvaluations($productModelId);
        $this->get('akeneo.pim.automation.data_quality_insights.create_product_models_criteria_evaluations')
            ->createAll([new ProductId($productModelId)]);
        $this->updateProductModelEvaluationsAt($productModelId, $createdAt, $status);
    }
}
