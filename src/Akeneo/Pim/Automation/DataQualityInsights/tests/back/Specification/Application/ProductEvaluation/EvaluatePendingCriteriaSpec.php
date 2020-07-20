<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaApplicabilityRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class EvaluatePendingCriteriaSpec extends ObjectBehavior
{
    public function let(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        CriteriaApplicabilityRegistry $applicabilityRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($repository, $evaluationRegistry, $applicabilityRegistry, $getPendingCriteriaEvaluationsQuery, $getEvaluableProductValuesQuery, $logger);
    }

    public function it_evaluates_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface $evaluateSpelling,
        EvaluateCriterionInterface $evaluateCompleteness
    ) {
        $criterionSpelling = new CriterionCode(EvaluateSpelling::CRITERION_CODE);
        $criterionCompleteness = new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);

        $criteria = [
            'product_42_spelling' => new Write\CriterionEvaluation(
                $criterionSpelling,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_42_completeness' => new Write\CriterionEvaluation(
                $criterionCompleteness,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_spelling' => new Write\CriterionEvaluation(
                $criterionSpelling,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            )
        ];

        $criteriaProduct42 = (new Write\CriterionEvaluationCollection())
            ->add($criteria['product_42_spelling'])
            ->add($criteria['product_42_completeness']);
        $criteriaProduct123 = (new Write\CriterionEvaluationCollection())
            ->add($criteria['product_123_spelling']);

        $getPendingCriteriaEvaluationsQuery->execute([42, 123])->willreturn([
            42 => $criteriaProduct42,
            123 => $criteriaProduct123
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId(new ProductId(42))->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId(new ProductId(123))->willReturn($product123Values);

        $evaluationRegistry->get($criterionSpelling)->willReturn($evaluateSpelling);
        $evaluationRegistry->get($criterionCompleteness)->willReturn($evaluateCompleteness);

        $evaluateSpelling->evaluate($criteria['product_42_spelling'], $product42Values)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateSpelling->evaluate($criteria['product_123_spelling'], $product123Values)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateCompleteness->evaluate($criteria['product_42_completeness'], $product42Values)
            ->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::any())->shouldBeCalledTimes(2);

        $this->evaluateAllCriteria([42, 123]);

        foreach ($criteria as $criterionEvaluation) {
            Assert::eq(CriterionEvaluationStatus::done(), $criterionEvaluation->getStatus());
        }
    }

    public function it_continues_to_evaluate_if_an_evaluation_failed(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface $evaluateCriterion
    ) {
        $criterionCode = new CriterionCode(EvaluateSpelling::CRITERION_CODE);

        $criterionA = new Write\CriterionEvaluation(
            $criterionCode,
            new ProductId(42),
            CriterionEvaluationStatus::pending()
        );

        $criterionB = new Write\CriterionEvaluation(
            $criterionCode,
            new ProductId(123),
            CriterionEvaluationStatus::pending()
        );

        $getPendingCriteriaEvaluationsQuery->execute([42, 123])->willreturn([
            42 => (new Write\CriterionEvaluationCollection())->add($criterionA),
            123 => (new Write\CriterionEvaluationCollection())->add($criterionB),
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId(new ProductId(42))->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId(new ProductId(123))->willReturn($product123Values);

        $evaluationRegistry->get($criterionCode)->willReturn($evaluateCriterion);
        $evaluateCriterion->evaluate($criterionA, $product42Values)->willThrow(new \Exception('Evaluation failed'));
        $evaluateCriterion->evaluate($criterionB, $product123Values)->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::any())->shouldBeCalledTimes(2);

        $this->evaluateAllCriteria([42, 123]);

        Assert::eq(CriterionEvaluationStatus::error(), $criterionA->getStatus());
        Assert::eq(CriterionEvaluationStatus::done(), $criterionB->getStatus());
    }

    public function it_evaluates_synchronous_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface $evaluateSpelling,
        EvaluateCriterionInterface $evaluateCompleteness
    ) {
        $criterionSpelling = new CriterionCode(EvaluateSpelling::CRITERION_CODE);

        $criteria = [
            'product_42_spelling' => new Write\CriterionEvaluation(
                $criterionSpelling,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_spelling' => new Write\CriterionEvaluation(
                $criterionSpelling,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            )
        ];

        $getPendingCriteriaEvaluationsQuery->execute([42, 123])->willreturn([
            42 => (new Write\CriterionEvaluationCollection())
                ->add($criteria['product_42_spelling']),
            123 => (new Write\CriterionEvaluationCollection())
                ->add($criteria['product_123_spelling']),
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId(new ProductId(42))->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId(new ProductId(123))->willReturn($product123Values);

        $evaluationRegistry->get($criterionSpelling)->willReturn($evaluateSpelling);
        $evaluateSpelling->evaluate($criteria['product_42_spelling'], $product42Values)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateSpelling->evaluate($criteria['product_123_spelling'], $product123Values)
            ->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::any())->shouldBeCalledTimes(2);

        $this->evaluateSynchronousCriteria([42, 123]);

        Assert::eq(CriterionEvaluationStatus::done(), $criteria['product_42_spelling']->getStatus());
        Assert::eq(CriterionEvaluationStatus::done(), $criteria['product_123_spelling']->getStatus());
    }

    private function givenRandomProductValues(): ProductValuesCollection
    {
        $attribute = new Attribute(new AttributeCode(strval(Uuid::uuid4())), AttributeType::text(), true);
        $values = (new ChannelLocaleDataCollection())
            ->addToChannelAndLocale(new ChannelCode('mobile'), new LocaleCode('en_US'), strval(Uuid::uuid4()))
            ->addToChannelAndLocale(new ChannelCode('print'), new LocaleCode('fr_FR'), strval(Uuid::uuid4()));

        return (new ProductValuesCollection())->add(new ProductValues($attribute, $values));
    }
}
