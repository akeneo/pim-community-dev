<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOutdatedProductModelIdsByDateAndCriteriaQueryIntegration extends DataQualityInsightsTestCase
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

    public function test_it_returns_outdated_product_model_ids_for_some_criteria(): void
    {
        $outdatedProductModel1 = $this->createProductModel('outdated_product_model_1', 'shoes_color');
        $outdatedProductModel2 = $this->createProductModel('outdated_product_model_2', 'shoes_color');
        $productModelWithoutEvaluation = $this->createProductModel('product_model_without_evaluation', 'shoes_color');
        $productModelWithoutEvaluationDate = $this->createProductModel('product_model_without_evaluation_date', 'shoes_color');
        $upToDateProductModel1 = $this->createProductModel('up_to_date_product_model_1', 'shoes_color');
        $upToDateProductModel2 = $this->createProductModel('up_to_date_product_model_2', 'shoes_color');
        $whateverProductModel = $this->createProductModel('whatever_product_model', 'shoes_color');

        $evaluationDate = $this->clock->fromString('2023-03-15 16:21:35');
        $criteria = [
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
            EvaluateImageEnrichment::CRITERION_CODE,
        ];

        $outdatedProductModelId1 = new ProductModelId($outdatedProductModel1->getId());
        $this->updateProductModelCriteriaEvaluationsAt($outdatedProductModelId1, $evaluationDate->modify('-1 second'), []);

        $outdatedProductModelId2 = new ProductModelId($outdatedProductModel2->getId());
        $this->updateProductModelCriteriaEvaluationsAt($outdatedProductModelId2, $evaluationDate->modify('+1 second'), []);
        $this->updateProductModelCriteriaEvaluationsAt($outdatedProductModelId2, $evaluationDate->modify('-1 second'), [EvaluateImageEnrichment::CRITERION_CODE]);

        $productModelWithoutEvaluationId = new ProductModelId($productModelWithoutEvaluation->getId());
        $this->deleteProductModelCriterionEvaluations($productModelWithoutEvaluationId->toInt());
        $productModelWithoutEvaluationDateId = new ProductModelId($productModelWithoutEvaluationDate->getId());

        $upToDateProductModelId1 = new ProductModelId($upToDateProductModel1->getId());
        $this->updateProductModelCriteriaEvaluationsAt($upToDateProductModelId1, $evaluationDate, []);

        $upToDateProductModelId2 = new ProductModelId($upToDateProductModel2->getId());
        $this->updateProductModelCriteriaEvaluationsAt($upToDateProductModelId2, $evaluationDate->modify('-1 second'), []);
        $this->updateProductModelCriteriaEvaluationsAt($upToDateProductModelId2, $evaluationDate->modify('+1 second'), $criteria);

        $this->updateProductModelCriteriaEvaluationsAt(new ProductModelId($whateverProductModel->getId()), $evaluationDate->modify('-1 hour'), []);

        $this->deleteProductModelCriterionEvaluations($productModelWithoutEvaluation->getId());

        $outdatedProductModels = ($this->get(GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface::class))(ProductModelIdCollection::fromProductModelIds([
            $outdatedProductModelId1,
            $outdatedProductModelId2,
            $productModelWithoutEvaluationId,
            $productModelWithoutEvaluationDateId,
            $upToDateProductModelId1,
            $upToDateProductModelId2,
        ]), $evaluationDate, $criteria);

        Assert::assertEqualsCanonicalizing(ProductModelIdCollection::fromProductModelIds([
            $outdatedProductModelId1,
            $outdatedProductModelId2,
            $productModelWithoutEvaluationId,
            $productModelWithoutEvaluationDateId,
        ])->toArrayString(), $outdatedProductModels->toArrayString());
    }

    public function test_it_returns_outdated_product_model_ids_for_any_criterion(): void
    {
        $outdatedProductModel1 = $this->createProductModel('outdated_product_model_1', 'shoes_color');
        $outdatedProductModel2 = $this->createProductModel('outdated_product_model_2', 'shoes_color');
        $upToDateProductModel = $this->createProductModel('up_to_date_product_model', 'shoes_color');

        $evaluationDate = $this->clock->fromString('2023-03-15 16:21:35');

        $outdatedProductModelId1 = new ProductModelId($outdatedProductModel1->getId());
        $this->updateProductModelCriteriaEvaluationsAt($outdatedProductModelId1, $evaluationDate->modify('-1 second'), []);

        $outdatedProductModelId2 = new ProductModelId($outdatedProductModel2->getId());
        $this->updateProductModelCriteriaEvaluationsAt($outdatedProductModelId2, $evaluationDate->modify('+1 second'), []);
        $this->updateProductModelCriteriaEvaluationsAt($outdatedProductModelId2, $evaluationDate->modify('-1 second'), [EvaluateImageEnrichment::CRITERION_CODE]);

        $upToDateProductModelId = new ProductModelId($upToDateProductModel->getId());
        $this->updateProductModelCriteriaEvaluationsAt($upToDateProductModelId, $evaluationDate, []);

        $outdatedProductModels = ($this->get(GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface::class))(ProductModelIdCollection::fromProductModelIds([
            $outdatedProductModelId1,
            $outdatedProductModelId2,
            $upToDateProductModelId,
        ]), $evaluationDate, []);

        Assert::assertEqualsCanonicalizing(ProductModelIdCollection::fromProductModelIds([
            $outdatedProductModelId1,
            $outdatedProductModelId2,
        ])->toArrayString(), $outdatedProductModels->toArrayString());
    }

    private function updateProductModelCriteriaEvaluationsAt(ProductModelId $productModelId, \DateTimeImmutable $evaluatedAt, array $criteria): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET evaluated_at = :evaluated_at 
WHERE product_id = :product_model_id
SQL;
        $queryParameters = [
            'product_model_id' => $productModelId->toInt(),
            'evaluated_at' => $evaluatedAt,
        ];

        $queryTypes = [
            'product_model_id' => Types::INTEGER,
            'evaluated_at' => Types::DATETIME_IMMUTABLE,
        ];

        if (!empty($criteria)) {
            $query .= ' AND criterion_code IN (:criteria)';
            $queryParameters['criteria'] = $criteria;
            $queryTypes['criteria'] = Connection::PARAM_STR_ARRAY;
        }

        $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);
    }
}
