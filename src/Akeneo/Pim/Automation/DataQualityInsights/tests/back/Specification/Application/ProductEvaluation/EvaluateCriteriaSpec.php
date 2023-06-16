<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateCriteriaSpec extends ObjectBehavior
{
    public function let(
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        CriteriaByFeatureRegistry $criteriaByFeatureRegistry,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        LoggerInterface $logger,
    ) {
        $this->beConstructedWith(
            $evaluationRegistry,
            $getEvaluableProductValuesQuery,
            $criteriaByFeatureRegistry,
            $criterionEvaluationRepository,
            $logger
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(EvaluateCriteria::class);
    }

    public function it_evaluates_products_on_given_criterion(
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface $evaluateService,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository
    ) {
        $productUuid1 = ProductUuid::fromUuid(Uuid::uuid4());
        $productUuid2 = ProductUuid::fromUuid(Uuid::uuid4());
        $criterionCode = new CriterionCode('criteria1');
        $entityValues1 = new ProductValuesCollection();
        $entityValues2 = new ProductValuesCollection();
        $result = new CriterionEvaluationResult();

        $getEvaluableProductValuesQuery->byProductId($productUuid1)->willReturn($entityValues1);
        $getEvaluableProductValuesQuery->byProductId($productUuid2)->willReturn($entityValues2);

        $evaluationRegistry->get($criterionCode)->shouldBeCalledTimes(2)->willReturn($evaluateService);

        $criterionEvaluation1 = new CriterionEvaluation($criterionCode, $productUuid1, CriterionEvaluationStatus::pending());
        $criterionEvaluation1->start();
        $evaluateService->evaluate($criterionEvaluation1, $entityValues1)
            ->shouldBeCalledOnce()
            ->willReturn($result)
        ;
        $criterionEvaluation2 = new CriterionEvaluation($criterionCode, $productUuid2, CriterionEvaluationStatus::pending());
        $criterionEvaluation2->start();
        $evaluateService->evaluate($criterionEvaluation2, $entityValues2)
            ->shouldBeCalledOnce()
            ->willReturn($result)
        ;
        $criterionEvaluationRepository->update(Argument::type(CriterionEvaluationCollection::class))
            ->shouldBeCalledTimes(2);

        $this->forEntityIds(ProductUuidCollection::fromProductUuids([$productUuid1, $productUuid2]), [$criterionCode]);
    }

    public function it_evaluates_product_on_all_criteria(
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        CriteriaByFeatureRegistry $criteriaByFeatureRegistry,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        EvaluateCriterionInterface $evaluateService1,
        EvaluateCriterionInterface $evaluateService2
    ) {
        $productUuid = ProductUuid::fromUuid(Uuid::uuid4());
        $criterionCode1 = new CriterionCode('criteria1');
        $criterionCode2 = new CriterionCode('criteria2');
        $entityValues = new ProductValuesCollection();
        $result = new CriterionEvaluationResult();

        $criteriaByFeatureRegistry->getAllCriterionCodes()->willReturn([$criterionCode1, $criterionCode2]);

        $getEvaluableProductValuesQuery->byProductId($productUuid)->willReturn($entityValues);

        $evaluationRegistry->get($criterionCode1)->shouldBeCalledOnce()->willReturn($evaluateService1);
        $criterionEvaluation1 = new CriterionEvaluation($criterionCode1, $productUuid, CriterionEvaluationStatus::pending());
        $criterionEvaluation1->start();
        $evaluateService1->evaluate($criterionEvaluation1, $entityValues)
            ->shouldBeCalledOnce()
            ->willReturn($result)
        ;

        $evaluationRegistry->get($criterionCode2)->shouldBeCalledOnce()->willReturn($evaluateService2);
        $criterionEvaluation2 = new CriterionEvaluation($criterionCode2, $productUuid, CriterionEvaluationStatus::pending());
        $criterionEvaluation2->start();
        $evaluateService2->evaluate($criterionEvaluation2, $entityValues)
            ->shouldBeCalledOnce()
            ->willReturn($result)
        ;

        $criterionEvaluationRepository->update(Argument::type(CriterionEvaluationCollection::class))
            ->shouldBeCalledOnce();

        $this->forEntityIds(ProductUuidCollection::fromProductUuids([$productUuid]), []);
    }
}
