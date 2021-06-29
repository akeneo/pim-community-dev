<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaApplicabilityRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\SynchronousCriterionEvaluationsFilterInterface;
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

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluatePendingCriteriaSpec extends ObjectBehavior
{
    public function let(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        CriteriaApplicabilityRegistry $applicabilityRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        SynchronousCriterionEvaluationsFilterInterface $synchronousCriterionEvaluationsFilter,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($repository, $evaluationRegistry, $applicabilityRegistry, $getPendingCriteriaEvaluationsQuery, $getEvaluableProductValuesQuery, $synchronousCriterionEvaluationsFilter, $logger);
    }

    public function it_evaluates_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $evaluationRegistry,
        GetPendingCriteriaEvaluationsByProductIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface $evaluateNonRequiredAttributeCompleteness,
        EvaluateCriterionInterface $evaluateCompleteness
    ) {
        $criterionNonRequiredAttributesCompleteness = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $criterionRequiredAttributesCompleteness = new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);

        $criteria = [
            'product_42_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributesCompleteness,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_42_completeness' => new Write\CriterionEvaluation(
                $criterionRequiredAttributesCompleteness,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributesCompleteness,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            )
        ];

        $criteriaProduct42 = (new Write\CriterionEvaluationCollection())
            ->add($criteria['product_42_non_required_att_completeness'])
            ->add($criteria['product_42_completeness']);
        $criteriaProduct123 = (new Write\CriterionEvaluationCollection())
            ->add($criteria['product_123_non_required_att_completeness']);

        $getPendingCriteriaEvaluationsQuery->execute([42, 123])->willreturn([
            42 => $criteriaProduct42,
            123 => $criteriaProduct123
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId(new ProductId(42))->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId(new ProductId(123))->willReturn($product123Values);

        $evaluationRegistry->get($criterionNonRequiredAttributesCompleteness)->willReturn($evaluateNonRequiredAttributeCompleteness);
        $evaluationRegistry->get($criterionRequiredAttributesCompleteness)->willReturn($evaluateCompleteness);

        $evaluateNonRequiredAttributeCompleteness->evaluate($criteria['product_42_non_required_att_completeness'], $product42Values)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateNonRequiredAttributeCompleteness->evaluate($criteria['product_123_non_required_att_completeness'], $product123Values)
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
        $criterionCode = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);

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
        SynchronousCriterionEvaluationsFilterInterface $synchronousCriterionEvaluationsFilter
    ) {
        $criterionNonRequiredAttributeCompleteness = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);

        $criteria = [
            'product_42_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributeCompleteness,
                new ProductId(42),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributeCompleteness,
                new ProductId(123),
                CriterionEvaluationStatus::pending()
            )
        ];

        $product42CriteriaCollection = (new Write\CriterionEvaluationCollection())->add($criteria['product_42_non_required_att_completeness']);
        $product123CriteriaCollection = (new Write\CriterionEvaluationCollection())->add($criteria['product_123_non_required_att_completeness']);
        $getPendingCriteriaEvaluationsQuery->execute([42, 123])->willreturn([
            42 => $product42CriteriaCollection,
            123 => $product123CriteriaCollection
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId(new ProductId(42))->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId(new ProductId(123))->willReturn($product123Values);

        $evaluationRegistry->get($criterionNonRequiredAttributeCompleteness)->willReturn($evaluateSpelling);
        $evaluateSpelling->evaluate($criteria['product_42_non_required_att_completeness'], $product42Values)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateSpelling->evaluate($criteria['product_123_non_required_att_completeness'], $product123Values)
            ->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::any())->shouldBeCalledTimes(2);

        $synchronousCriterionEvaluationsFilter->filter($product42CriteriaCollection->getIterator())->willReturn([
            $criteria['product_42_non_required_att_completeness'],
        ]);
        $synchronousCriterionEvaluationsFilter->filter($product123CriteriaCollection->getIterator())->willReturn([
            $criteria['product_123_non_required_att_completeness'],
        ]);

        $this->evaluateSynchronousCriteria([42, 123]);

        Assert::eq($criteria['product_42_non_required_att_completeness']->getStatus(), CriterionEvaluationStatus::done());
        Assert::eq($criteria['product_123_non_required_att_completeness']->getStatus(), CriterionEvaluationStatus::done());
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
