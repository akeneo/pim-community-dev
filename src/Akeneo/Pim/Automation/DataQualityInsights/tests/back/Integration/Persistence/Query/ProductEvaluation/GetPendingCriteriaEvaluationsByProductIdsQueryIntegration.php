<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class GetPendingCriteriaEvaluationsByProductIdsQueryIntegration extends DataQualityInsightsTestCase
{
    private CriterionEvaluationRepositoryInterface $productCriterionEvaluationRepository;

    private CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
        $this->productModelCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_model_criterion_evaluation');
    }

    public function test_it_finds_product_pending_criteria_evaluations()
    {
        $productIdA = $this->givenAProductWithTwoPendingAndOneDoneEvaluations('product_A');
        $productIdB = $this->givenAProductWithOnePendingEvaluation('product_B');
        $productIdC = $this->givenAProductWithOnlyDoneEvaluations('product_C');

        $this->givenAProductWithOnePendingEvaluation('not_involved_product');

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
            ->execute([$productIdA, $productIdB, $productIdC]);

        $this->assertCount(2, $evaluations);
        $this->assertArrayHasKey($productIdA, $evaluations);
        $this->assertArrayHasKey($productIdB, $evaluations);
        $this->assertCount(2, $evaluations[$productIdA]);
        $this->assertCount(1, $evaluations[$productIdB]);
    }

    public function test_it_finds_product_models_pending_criteria_evaluations()
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');

        $productModelIdA = $this->givenAProductModelWithTwoPendingAndOneDoneEvaluations('product_A');
        $productModelIdB = $this->givenAProductModelWithOnePendingEvaluation('product_B');
        $productModelIdC = $this->givenAProductModelWithOnlyDoneEvaluations('product_C');

        $this->givenAProductModelWithOnePendingEvaluation('not_involved_product_model');

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_pending_criteria_evaluations')
            ->execute([$productModelIdA, $productModelIdB, $productModelIdC]);

        $this->assertCount(2, $evaluations);
        $this->assertArrayHasKey($productModelIdA, $evaluations);
        $this->assertArrayHasKey($productModelIdB, $evaluations);
        $this->assertCount(2, $evaluations[$productModelIdA]);
        $this->assertCount(1, $evaluations[$productModelIdB]);
    }

    public function test_it_returns_an_empty_array_if_there_is_no_pending_criteria_evaluations()
    {
        $this->assertEmpty($this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
            ->execute([42]));
    }

    private function givenAProductWithTwoPendingAndOneDoneEvaluations(string $productCode): int
    {
        $productId = new ProductId($this->createProductWithoutEvaluations($productCode)->getId());

        $criterionEvaluationCollection = $this->createTwoPendingAndOneDoneEvaluations($productId);
        $this->productCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productId->toInt();
    }

    private function givenAProductModelWithTwoPendingAndOneDoneEvaluations(string $productModelCode): int
    {
        $productModelId = new ProductId($this->createProductModelWithoutEvaluations($productModelCode, 'a_family_variant')->getId());

        $criterionEvaluationCollection = $this->createTwoPendingAndOneDoneEvaluations($productModelId);
        $this->productModelCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productModelId->toInt();
    }

    private function createTwoPendingAndOneDoneEvaluations(ProductId $productId): Write\CriterionEvaluationCollection
    {
        return (new Write\CriterionEvaluationCollection())
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productId,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                $productId,
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('lower_case'),
                $productId,
                CriterionEvaluationStatus::done()
            ));
    }

    private function givenAProductWithOnePendingEvaluation(string $productCode): int
    {
        $productId = new ProductId($this->createProductWithoutEvaluations($productCode)->getId());

        $criterionEvaluationCollection = (new Write\CriterionEvaluationCollection())
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productId,
                CriterionEvaluationStatus::pending()
            ));

        $this->productCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productId->toInt();
    }

    private function givenAProductModelWithOnePendingEvaluation(string $productModelCode): int
    {
        $productModelId = new ProductId($this->createProductModelWithoutEvaluations($productModelCode, 'a_family_variant')->getId());

        $criterionEvaluationCollection = (new Write\CriterionEvaluationCollection())
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productModelId,
                CriterionEvaluationStatus::pending()
            ));

        $this->productModelCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productModelId->toInt();
    }

    private function givenAProductWithOnlyDoneEvaluations(string $productCode): int
    {
        $productId = $this->createProductWithoutEvaluations($productCode)->getId();

        $evaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            new ProductId($productId),
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection())->add($evaluation);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $evaluation->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update($evaluations);

        return $productId;
    }

    private function givenAProductModelWithOnlyDoneEvaluations(string $productModelCode): int
    {
        $productModelId = $this->createProductModelWithoutEvaluations($productModelCode, 'a_family_variant')->getId();

        $evaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            new ProductId($productModelId),
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection())->add($evaluation);
        $this->productModelCriterionEvaluationRepository->create($evaluations);

        $evaluation->end(new Write\CriterionEvaluationResult());
        $this->productModelCriterionEvaluationRepository->update($evaluations);

        return $productModelId;
    }
}
