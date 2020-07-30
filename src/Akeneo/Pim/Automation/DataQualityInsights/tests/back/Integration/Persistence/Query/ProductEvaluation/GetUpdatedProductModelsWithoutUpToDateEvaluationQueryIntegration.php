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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpdatedProductModelsWithoutUpToDateEvaluationQuery;
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

        $productModelIds = iterator_to_array($this->query->execute($today->modify('-1 DAY'), 2));

        $this->assertCount(2, $productModelIds);
        $this->assertCount(2, $productModelIds[0]);
        $this->assertCount(1, $productModelIds[1]);
        $productModelIds = array_merge($productModelIds[0], $productModelIds[1]);

        $this->assertExpectedProductModelId($expectedProductModel1, $productModelIds);
        $this->assertExpectedProductModelId($expectedProductModel2, $productModelIds);
        $this->assertExpectedProductModelId($expectedProductModel3, $productModelIds);
    }

    private function createProductModel(string $familyVariant): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId((int) $productModel->getId());
    }

    private function updateProductModelEvaluationsAt(ProductId $productModelId, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pimee_data_quality_insights_product_model_criteria_evaluation SET evaluated_at = :evaluated_at WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productModelId->toInt(),
        ]);
    }

    private function removeProductModelEvaluations(ProductId $productModelId): void
    {
        $query = <<<SQL
DELETE FROM pimee_data_quality_insights_product_model_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productModelId->toInt(),]);
    }

    private function updateProductModelAt(ProductId $productModelId, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :updated WHERE id = :product_model_id;
SQL;

        $this->db->executeQuery($query, [
            'updated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_model_id' => $productModelId->toInt(),
        ]);
    }

    private function givenAProductModelWithoutAnyEvaluation(): ProductId
    {
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->removeProductModelEvaluations($productModelId);

        return $productModelId;
    }

    private function givenAnUpdatedProductModelWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('-1 SECOND'));

        return $productModelId;
    }

    private function givenAnUpdatedProductModelWithAnUpToDateEvaluation(\DateTimeImmutable $today)
    {
        $updatedAt = $today->modify('-2 SECOND');
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('+1 SECOND'));
    }

    private function givenAnOldUpdatedProductModelWithAnOutdatedEvaluation(\DateTimeImmutable $today)
    {
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $today->modify('-2 DAY'));
        $this->createProductModelCriteriaEvaluations($productModelId, $today->modify('-3 DAY'));
    }

    private function givenAParentProductModel(string $productModelCode, \DateTimeImmutable $updatedAt)
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode($productModelCode)
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $productModelId = new ProductId($productModel->getId());

        $this->updateProductModelAt(new ProductId($productModel->getId()), $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('+1 SECOND'));
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

        $productModelId = new ProductId($productModel->getId());

        $this->updateProductModelAt($productModelId, $updatedAt->modify('-1 HOUR'));
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('-1 SECOND'));

        return $productModelId;
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

    private function createProductModelCriteriaEvaluations(ProductId $productModelId, \DateTimeImmutable $createdAt): void
    {
        $this->removeProductModelEvaluations($productModelId);
        $this->get('akeneo.pim.automation.data_quality_insights.create_product_models_criteria_evaluations')->createAll([$productModelId]);
        $this->updateProductModelEvaluationsAt($productModelId, $createdAt);
    }
}
