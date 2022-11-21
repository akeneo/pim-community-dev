<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductUuidsToEvaluateQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

class GetProductUuidsToEvaluateQueryIntegration extends DataQualityInsightsTestCase
{
    private GetProductUuidsToEvaluateQuery $productQuery;

    private CriterionEvaluationRepositoryInterface $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productQuery = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_ids_to_evaluate');
        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
    }

    public function test_it_returns_all_product_id_with_pending_criteria()
    {
        $this->givenAProductWithEvaluationDone();
        $this->assertEquals([], iterator_to_array($this->productQuery->execute(4, 2)), 'All products evaluations should be done');

        $expectedProductUuids = $this->givenThreeProductsToEvaluate();

        $productUuids = iterator_to_array($this->productQuery->execute(4, 2));
        $productUuids = array_map(fn (ProductUuidCollection $collection) => $collection->toArrayString(), $productUuids);

        $this->assertCount(2, $productUuids);
        $this->assertCount(2, $productUuids[0]);
        $this->assertEqualsCanonicalizing($expectedProductUuids, array_merge_recursive(...$productUuids));
    }

    private function givenThreeProductsToEvaluate(): array
    {
        $productUuid1 = $this->createProductWithoutEvaluations('product_1')->getUuid();
        $productUuid2 = $this->createProductWithoutEvaluations('product_2')->getUuid();
        $productUuid3 = $this->createProductWithoutEvaluations('product_3')->getUuid();

        $evaluations = (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                ProductUuid::fromUuid($productUuid1),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                ProductUuid::fromUuid($productUuid1),
                CriterionEvaluationStatus::done()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                ProductUuid::fromUuid($productUuid2),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                ProductUuid::fromUuid($productUuid3),
                CriterionEvaluationStatus::pending()
            ));

        $this->productCriterionEvaluationRepository->create($evaluations);

        return [$productUuid1, $productUuid2, $productUuid3];
    }

    private function givenAProductWithEvaluationDone(): void
    {
        $productUuid = $this->createProductWithoutEvaluations('product_with_evaluations_done')->getUuid();

        $evaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            ProductUuid::fromUuid($productUuid),
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection)->add($evaluationDone);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $evaluationDone->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update($evaluations);
    }
}
