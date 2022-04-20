<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductIdsToEvaluateQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

class GetProductIdsToEvaluateQueryIntegration extends DataQualityInsightsTestCase
{
    private GetProductIdsToEvaluateQuery $productQuery;

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

        $expectedProductIds = $this->givenThreeProductsToEvaluate();

        $productIds = iterator_to_array($this->productQuery->execute(4, 2));
        $productIds = array_map(fn (ProductUuidCollection $collection) => $collection->toArrayString(), $productIds);

        $this->assertCount(2, $productIds);
        $this->assertCount(2, $productIds[0]);
        $this->assertEqualsCanonicalizing($expectedProductIds, array_merge_recursive(...$productIds));
    }

    private function givenThreeProductsToEvaluate(): array
    {
        $productUuid1= $this->createProductWithoutEvaluations('product_1')->getUuid();
        $productUuid2 = $this->createProductWithoutEvaluations('product_2')->getUuid();
        $productUuid3 = $this->createProductWithoutEvaluations('product_3')->getUuid();

        $evaluations = (new Write\CriterionEvaluationCollection)
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                new ProductUuid($productUuid1),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('spelling'),
                new ProductUuid($productUuid1),
                CriterionEvaluationStatus::done()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductUuid($productUuid2),
                CriterionEvaluationStatus::pending()
            ))
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completion'),
                new ProductUuid($productUuid3),
                CriterionEvaluationStatus::pending()
            ));

        $this->productCriterionEvaluationRepository->create($evaluations);

        return [$productUuid1, $productUuid2, $productUuid3];
    }

    private function givenAProductWithEvaluationDone(): void
    {
        $productId = $this->createProductWithoutEvaluations('product_with_evaluations_done')->getId();

        $evaluationDone = new Write\CriterionEvaluation(
            new CriterionCode('completeness'),
            new ProductUuid($productId),
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection)->add($evaluationDone);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $evaluationDone->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update($evaluations);
    }
}
