<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\HasUpToDateProductModelEvaluationQuery;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HasUpToDateProductModelEvaluationQueryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var HasUpToDateProductModelEvaluationQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->query = $this->get(HasUpToDateProductModelEvaluationQuery::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_true_if_a_root_product_model_has_an_up_to_date_evaluation()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId1 = $this->givenAProductModelWithAnUpToDateEvaluation($currentDate);
        $this->givenAnUpdatedProductModelWithAnOutdatedEvaluation($currentDate);

        $productHasUpToDateEvaluation = $this->query->forProductId($productId1);
        $this->assertTrue($productHasUpToDateEvaluation);
    }

    public function test_it_returns_false_if_a_root_product_model_has_an_outdated_evaluation()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $this->givenAProductModelWithAnUpToDateEvaluation($currentDate);
        $productId1 = $this->givenAnUpdatedProductModelWithAnOutdatedEvaluation($currentDate);

        $productHasUpToDateEvaluation = $this->query->forProductId($productId1);
        $this->assertFalse($productHasUpToDateEvaluation);
    }

    public function test_it_returns_false_if_a_sub_product_model_has_an_outdated_evaluation_because_of_its_parent()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId1 = $this->givenASubProductModelWithAnOutdatedEvaluationComparedToItsParent($currentDate);
        $productHasUpToDateEvaluation = $this->query->forProductId($productId1);
        $this->assertFalse($productHasUpToDateEvaluation);
    }

    public function test_it_returns_true_if_a_sub_product_model_has_an_up_to_date_evaluation_because_of_its_parent()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId1 = $this->givenASubProductModelWithAnUpToDateEvaluationComparedToItsParent($currentDate);
        $productHasUpToDateEvaluation = $this->query->forProductId($productId1);
        $this->assertTrue($productHasUpToDateEvaluation);
    }

    private function givenAProductModelWithAnUpToDateEvaluation(\DateTimeImmutable $currentDate): ProductId
    {
        $updatedAt = $currentDate->modify('-2 SECOND');
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('+1 SECOND'));

        return $productModelId;
    }

    private function givenAnUpdatedProductModelWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductId
    {
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('-1 SECOND'));

        return $productModelId;
    }

    private function givenASubProductModelWithAnOutdatedEvaluationComparedToItsParent(\DateTimeImmutable $updatedAt): ProductId
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

    private function givenASubProductModelWithAnUpToDateEvaluationComparedToItsParent(\DateTimeImmutable $updatedAt): ProductId
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
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('+1 MINUTE'));

        return $productModelId;
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

    private function createProductModel(string $familyVariant): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId((int) $productModel->getId());
    }

    private function updateProductModelAt(ProductId $productModelId, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :evaluated WHERE id = :product_model_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_model_id' => $productModelId->toInt(),
        ]);
    }

    private function createProductModelCriteriaEvaluations(ProductId $productModelId, \DateTimeImmutable $createdAt): void
    {
        $this->removeProductModelEvaluations($productModelId);
        $this->get('akeneo.pim.automation.data_quality_insights.create_product_models_criteria_evaluations')->createAll([$productModelId]);
        $this->updateProductModelEvaluationsAt($productModelId, $createdAt);
    }

    private function removeProductModelEvaluations(ProductId $productModelId): void
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productModelId->toInt(),]);
    }

    private function updateProductModelEvaluationsAt(ProductId $productModelId, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation SET evaluated_at = :evaluated_at WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productModelId->toInt(),
        ]);
    }
}
