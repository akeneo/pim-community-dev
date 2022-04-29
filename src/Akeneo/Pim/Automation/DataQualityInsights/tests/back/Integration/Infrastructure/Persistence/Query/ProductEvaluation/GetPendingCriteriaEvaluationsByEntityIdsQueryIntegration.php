<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductModelIdFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Ramsey\Uuid\Uuid;

final class GetPendingCriteriaEvaluationsByEntityIdsQueryIntegration extends DataQualityInsightsTestCase
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

        $productIdCollection = $this->get(ProductUuidFactory::class)->createCollection([
            (string)$productIdA, (string)$productIdB, (string)$productIdC
        ]);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
            ->execute($productIdCollection);

        $this->assertCount(2, $evaluations);
        $this->assertArrayHasKey((string)$productIdA, $evaluations);
        $this->assertArrayHasKey((string)$productIdB, $evaluations);
        $this->assertCount(2, $evaluations[(string)$productIdA]);
        $this->assertCount(1, $evaluations[(string)$productIdB]);
    }

    public function test_it_finds_product_models_pending_criteria_evaluations()
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');

        $productModelIdA = $this->givenAProductModelWithTwoPendingAndOneDoneEvaluations('product_A');
        $productModelIdB = $this->givenAProductModelWithOnePendingEvaluation('product_B');
        $productModelIdC = $this->givenAProductModelWithOnlyDoneEvaluations('product_C');

        $this->givenAProductModelWithOnePendingEvaluation('not_involved_product_model');

        $productModelIdCollection = $this->get(ProductModelIdFactory::class)->createCollection([
            (string)$productModelIdA, (string)$productModelIdB, (string)$productModelIdC
        ]);

        $evaluations = $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_pending_criteria_evaluations')
            ->execute($productModelIdCollection);

        $this->assertCount(2, $evaluations);
        $this->assertArrayHasKey((string)$productModelIdA, $evaluations);
        $this->assertArrayHasKey((string)$productModelIdB, $evaluations);
        $this->assertCount(2, $evaluations[(string)$productModelIdA]);
        $this->assertCount(1, $evaluations[(string)$productModelIdB]);
    }

    public function test_it_returns_an_empty_array_if_there_is_no_pending_criteria_evaluations()
    {
        $productIdCollection = $this->get(ProductUuidFactory::class)->createCollection([Uuid::uuid4()->toString()]);
        $this->assertEmpty($this->get('akeneo.pim.automation.data_quality_insights.query.get_product_pending_criteria_evaluations')
            ->execute($productIdCollection));
    }

    private function givenAProductWithTwoPendingAndOneDoneEvaluations(string $productCode): ProductUuid
    {
        $product = $this->createProductWithoutEvaluations($productCode);
        $productUuid = $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());

        $criterionEvaluationCollection = $this->createTwoPendingAndOneDoneEvaluations($productUuid);
        $this->productCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productUuid;
    }

    private function givenAProductModelWithTwoPendingAndOneDoneEvaluations(string $productModelCode): ProductModelId
    {
        $productModel = $this->createProductModelWithoutEvaluations($productModelCode, 'a_family_variant');
        $productModelId = $this->get(ProductModelIdFactory::class)->create((string)$productModel->getId());

        $criterionEvaluationCollection = $this->createTwoPendingAndOneDoneEvaluations($productModelId);
        $this->productModelCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productModelId;
    }

    private function createTwoPendingAndOneDoneEvaluations(ProductEntityIdInterface $productId): Write\CriterionEvaluationCollection
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

    private function givenAProductWithOnePendingEvaluation(string $productCode): ProductUuid
    {
        $product = $this->createProductWithoutEvaluations($productCode);
        $productUuid = $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());

        $criterionEvaluationCollection = (new Write\CriterionEvaluationCollection())
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productUuid,
                CriterionEvaluationStatus::pending()
            ));

        $this->productCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productUuid;
    }

    private function givenAProductModelWithOnePendingEvaluation(string $productModelCode): ProductModelId
    {
        $productModel = $this->createProductModelWithoutEvaluations($productModelCode, 'a_family_variant');
        $productModelId = $this->get(ProductModelIdFactory::class)->create((string)$productModel->getId());

        $criterionEvaluationCollection = (new Write\CriterionEvaluationCollection())
            ->add(new Write\CriterionEvaluation(
                new CriterionCode('completeness'),
                $productModelId,
                CriterionEvaluationStatus::pending()
            ));

        $this->productModelCriterionEvaluationRepository->create($criterionEvaluationCollection);

        return $productModelId;
    }

    private function givenAProductWithOnlyDoneEvaluations(string $productCode): ProductUuid
    {
        $product = $this->createProductWithoutEvaluations($productCode);
        $productUuid = $this->get(ProductUuidFactory::class)->create((string)$product->getUuid());

        $evaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productUuid,
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection())->add($evaluation);
        $this->productCriterionEvaluationRepository->create($evaluations);

        $evaluation->end(new Write\CriterionEvaluationResult());
        $this->productCriterionEvaluationRepository->update($evaluations);

        return $productUuid;
    }

    private function givenAProductModelWithOnlyDoneEvaluations(string $productModelCode): ProductModelId
    {
        $productModel = $this->createProductModelWithoutEvaluations($productModelCode, 'a_family_variant');
        $productModelId = $this->get(ProductModelIdFactory::class)->create((string)$productModel->getId());

        $evaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            $productModelId,
            CriterionEvaluationStatus::pending()
        );

        $evaluations = (new Write\CriterionEvaluationCollection())->add($evaluation);
        $this->productModelCriterionEvaluationRepository->create($evaluations);

        $evaluation->end(new Write\CriterionEvaluationResult());
        $this->productModelCriterionEvaluationRepository->update($evaluations);

        return $productModelId;
    }
}
