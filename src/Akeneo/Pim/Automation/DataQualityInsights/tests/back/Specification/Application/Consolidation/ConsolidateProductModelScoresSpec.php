<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ComputeScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\FilterPartialCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductModelScoreRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsolidateProductModelScoresSpec extends ObjectBehavior
{
    public function let(
        GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsQuery,
        ComputeScores                                   $computeScores,
        ProductModelScoreRepositoryInterface            $productModelScoreRepository,
        Clock                                           $clock,
        FilterPartialCriteriaEvaluations $filterCriteriaEvaluationsForPartialScore
    ) {
        $this->beConstructedWith($getCriteriaEvaluationsQuery, $computeScores, $productModelScoreRepository, $clock, $filterCriteriaEvaluationsForPartialScore);
    }

    public function it_consolidates_product_model_scores(
        GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsQuery,
        ComputeScores $computeScores,
        ProductModelScoreRepositoryInterface $productModelScoreRepository,
        Clock $clock,
        $filterCriteriaEvaluationsForPartialScore
    ) {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');

        $productModelId1 = new ProductModelId(42);
        $productModelId2 = new ProductModelId(56);

        $clock->getCurrentTime()->willReturn(new \DateTimeImmutable());

        $scores1 = (new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(93));
        $productModelId1Evaluations = $this->givenACriterionEvaluationCollection($productModelId1);
        $filterCriteriaEvaluationsForPartialScore->__invoke($productModelId1Evaluations)->willReturn($productModelId1Evaluations);

        $getCriteriaEvaluationsQuery->execute($productModelId1)->willReturn($productModelId1Evaluations);
        $computeScores->fromCriteriaEvaluations($productModelId1Evaluations)->willReturn($scores1);

        $scores2 = (new ChannelLocaleRateCollection())->addRate($channelMobile, $localeEn, new Rate(65));
        $productModelId2Evaluations = $this->givenACriterionEvaluationCollection($productModelId2);
        $filterCriteriaEvaluationsForPartialScore->__invoke($productModelId2Evaluations)->willReturn($productModelId2Evaluations);

        $getCriteriaEvaluationsQuery->execute($productModelId2)->willReturn($productModelId2Evaluations);
        $computeScores->fromCriteriaEvaluations($productModelId2Evaluations)->willReturn($scores2);

        $productModelScoreRepository->saveAll(Argument::that(function (array $productModelScores) use ($productModelId1, $productModelId2, $scores1, $scores2) {
            return 2 === count($productModelScores)
                && $productModelScores[0] instanceof Write\ProductScores && (string) $productModelId1 === (string) $productModelScores[0]->getEntityId() && $scores1 === $productModelScores[0]->getScores()
                && $productModelScores[1] instanceof Write\ProductScores && (string) $productModelId2 === (string) $productModelScores[1]->getEntityId() && $scores2 === $productModelScores[1]->getScores();
        }))->shouldBeCalled();

        $this->consolidate(ProductModelIdCollection::fromStrings([$productModelId1, $productModelId2]));
    }

    private function givenACriterionEvaluationCollection(ProductEntityIdInterface $entityId): Read\CriterionEvaluationCollection
    {
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');

        $criterionResultA = (new ChannelLocaleRateCollection)->addRate($channelMobile, $localeEn, new Rate(100));
        $criterionResultB = (new ChannelLocaleRateCollection)->addRate($channelMobile, $localeEn, new Rate(90));

        $criterionA = new CriterionCode('criterion_A');
        $criterionB = new CriterionCode('criterion_B');

        return (new Read\CriterionEvaluationCollection())
            ->add(new Read\CriterionEvaluation(
                $criterionA,
                $entityId,
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new Read\CriterionEvaluationResult($criterionResultA, new CriterionEvaluationResultStatusCollection(), [])
            ))
            ->add(new Read\CriterionEvaluation(
                $criterionB,
                $entityId,
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new Read\CriterionEvaluationResult($criterionResultB, new CriterionEvaluationResultStatusCollection(), [])
            ));
    }
}
