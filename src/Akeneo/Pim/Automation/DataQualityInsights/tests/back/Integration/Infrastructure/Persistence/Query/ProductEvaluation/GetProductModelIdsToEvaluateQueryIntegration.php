<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelIdsToEvaluateQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

class GetProductModelIdsToEvaluateQueryIntegration extends DataQualityInsightsTestCase
{
    private GetProductModelIdsToEvaluateQuery $productModelQuery;

    private CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelQuery = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_ids_to_evaluate');
        $this->productModelCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
    }

    public function test_it_returns_all_product_models_id_with_pending_criteria()
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');
        $this->givenAProductModelWithEvaluationDone();

        $this->assertEquals([], iterator_to_array($this->productModelQuery->execute(4, 2)), 'All product models evaluations should be done');

        $expectedProductIds = $this->givenThreeProductModelsToEvaluate();
        $productIds = iterator_to_array($this->productModelQuery->execute(4, 2));
        $productIds = array_map(fn(ProductModelIdCollection $collection) => $collection->toArrayString(), $productIds);

        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertEqualsCanonicalizing($expectedProductIds, array_merge_recursive(...$productIds));
    }

    private function givenThreeProductModelsToEvaluate(): array
    {
        $productModelId1 = $this->createProductModelWithoutEvaluations('product_model_1', 'a_family_variant')->getId();
        $productModelId2 = $this->createProductModelWithoutEvaluations('product_model_2', 'a_family_variant')->getId();
        $productModelId3 = $this->createProductModelWithoutEvaluations('product_model_3', 'a_family_variant')->getId();

        $evaluations = (new CriterionEvaluationCollection)
            ->add(new CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductModelId($productModelId1),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('spelling'),
                new ProductModelId($productModelId1),
                CriterionEvaluationStatus::done()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductModelId($productModelId2),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductModelId($productModelId3),
                CriterionEvaluationStatus::pending()
            ));

        $this->productModelCriterionEvaluationRepository->create($evaluations);

        return [$productModelId1, $productModelId2, $productModelId3];
    }

    private function givenAProductModelWithEvaluationDone(): void
    {
        $productModelId = $this->createProductModelWithoutEvaluations('product_model_with_evaluations_done', 'a_family_variant')->getId();

        $evaluationDone = new CriterionEvaluation(
            new CriterionCode('completeness'),
            ProductModelId::fromString((string) $productModelId),
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new CriterionEvaluationCollection)->add($evaluationDone);
        $this->productModelCriterionEvaluationRepository->create($evaluations);

        $evaluationDone->end(new CriterionEvaluationResult());
        $this->productModelCriterionEvaluationRepository->update($evaluations);
    }
}
