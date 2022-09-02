<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
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

    public function testItReturnsTrueIfARootProductModelHasAnUpToDateEvaluation()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId1 = $this->givenAProductModelWithAnUpToDateEvaluation($currentDate);
        $this->givenAnUpdatedProductModelWithAnOutdatedEvaluation($currentDate);

        $productHasUpToDateEvaluation = $this->query->forEntityId($productId1);
        $this->assertTrue($productHasUpToDateEvaluation);
    }

    public function testItReturnsFalseIfARootProductModelHasAnOutdatedEvaluation()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $this->givenAProductModelWithAnUpToDateEvaluation($currentDate);
        $productId1 = $this->givenAnUpdatedProductModelWithAnOutdatedEvaluation($currentDate);

        $productHasUpToDateEvaluation = $this->query->forEntityId($productId1);
        $this->assertFalse($productHasUpToDateEvaluation);
    }

    public function testItReturnsFalseIfASubProductModelHasAnOutdatedEvaluationBecauseOfItsParent()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId1 = $this->givenASubProductModelWithAnOutdatedEvaluationComparedToItsParent($currentDate);
        $productHasUpToDateEvaluation = $this->query->forEntityId($productId1);
        $this->assertFalse($productHasUpToDateEvaluation);
    }

    public function testItReturnsTrueIfASubProductModelHasAnUpToDateEvaluationBecauseOfItsParent()
    {
        $currentDate = new \DateTimeImmutable('2020-03-02 11:34:27');

        $productId1 = $this->givenASubProductModelWithAnUpToDateEvaluationComparedToItsParent($currentDate);
        $productHasUpToDateEvaluation = $this->query->forEntityId($productId1);
        $this->assertTrue($productHasUpToDateEvaluation);
    }

    private function givenAProductModelWithAnUpToDateEvaluation(\DateTimeImmutable $currentDate): ProductModelId
    {
        $updatedAt = $currentDate->modify('-2 SECOND');
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('+1 SECOND'));

        return $productModelId;
    }

    private function givenAnUpdatedProductModelWithAnOutdatedEvaluation(\DateTimeImmutable $updatedAt): ProductModelId
    {
        $productModelId = $this->createProductModel('familyVariantA2');
        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('-1 SECOND'));

        return $productModelId;
    }

    private function givenASubProductModelWithAnOutdatedEvaluationComparedToItsParent(\DateTimeImmutable $updatedAt): ProductModelId
    {
        $parentCode = strval(Uuid::uuid4());
        $this->givenAParentProductModel($parentCode, $updatedAt);

        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant('familyVariantA1')
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $productModelId = $this->get(ProductModelIdFactory::class)->create((string) $productModel->getId());

        $this->updateProductModelAt($productModelId, $updatedAt->modify('-1 HOUR'));
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('-1 SECOND'));

        return $productModelId;
    }

    private function givenASubProductModelWithAnUpToDateEvaluationComparedToItsParent(\DateTimeImmutable $updatedAt): ProductModelId
    {
        $parentCode = strval(Uuid::uuid4());
        $this->givenAParentProductModel($parentCode, $updatedAt);

        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant('familyVariantA1')
            ->withParent($parentCode)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $productModelId = $this->get(ProductModelIdFactory::class)->create((string) $productModel->getId());

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

        $productModelId = $this->get(ProductModelIdFactory::class)->create((string) $productModel->getId());

        $this->updateProductModelAt($productModelId, $updatedAt);
        $this->createProductModelCriteriaEvaluations($productModelId, $updatedAt->modify('+1 SECOND'));
    }

    private function createProductModel(string $familyVariant): ProductModelId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $this->get(ProductModelIdFactory::class)->create((string) $productModel->getId());
    }

    private function updateProductModelAt(ProductModelId $productModelId, \DateTimeImmutable $updatedAt)
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model SET updated = :evaluated WHERE id = :product_model_id;
SQL;

        $this->db->executeQuery($query, [
            'evaluated' => $updatedAt->format('Y-m-d H:i:s'),
            'product_model_id' => $productModelId->toInt(),
        ]);
    }

    private function createProductModelCriteriaEvaluations(ProductModelId $productModelId, \DateTimeImmutable $createdAt): void
    {
        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([(string) $productModelId]);
        $this->removeProductModelEvaluations($productModelId);
        $this->get('akeneo.pim.automation.data_quality_insights.create_product_models_criteria_evaluations')
            ->createAll($productModelIdCollection);
        $this->updateProductModelEvaluationsAt($productModelId, $createdAt);
    }

    private function removeProductModelEvaluations(ProductModelId $productModelId): void
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation WHERE product_id = :product_id;
SQL;

        $this->db->executeQuery($query, ['product_id' => $productModelId->toInt()]);
    }

    private function updateProductModelEvaluationsAt(ProductModelId $productModelId, \DateTimeImmutable $evaluatedAt): void
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
