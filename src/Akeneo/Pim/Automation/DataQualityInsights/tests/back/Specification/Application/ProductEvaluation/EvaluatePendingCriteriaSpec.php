<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaApplicabilityRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\SynchronousCriterionEvaluationsFilterInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetPendingCriteriaEvaluationsByEntityIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
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
        CriterionEvaluationRepositoryInterface                 $repository,
        CriteriaEvaluationRegistry                             $evaluationRegistry,
        CriteriaApplicabilityRegistry                          $applicabilityRegistry,
        GetPendingCriteriaEvaluationsByEntityIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface                $getEvaluableProductValuesQuery,
        SynchronousCriterionEvaluationsFilterInterface         $synchronousCriterionEvaluationsFilter,
        LoggerInterface                                        $logger,
        ProductEntityIdFactoryInterface                        $idFactory
    )
    {
        $this->beConstructedWith($repository, $evaluationRegistry, $applicabilityRegistry, $getPendingCriteriaEvaluationsQuery, $getEvaluableProductValuesQuery, $synchronousCriterionEvaluationsFilter, $logger, $idFactory);
    }

    public function it_evaluates_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface                 $repository,
        CriteriaEvaluationRegistry                             $evaluationRegistry,
        GetPendingCriteriaEvaluationsByEntityIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface                $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface                             $evaluateNonRequiredAttributeCompleteness,
        EvaluateCriterionInterface                             $evaluateCompleteness,
        ProductEntityIdFactoryInterface                        $idFactory,
        ProductEntityIdCollection                              $productIdCollection,
        ProductEntityIdInterface                               $productId_fef37e64,
        ProductEntityIdInterface                               $productIdB
    ) {
        $productIdCollection->isEmpty()->willReturn(false);
        $productIdCollection->toArrayString()->willReturn(['fef37e64-a963-47a9-b087-2cc67968f0a2', 'df470d52-7723-4890-85a0-e79be625e2ed']);

        $criterionNonRequiredAttributesCompleteness = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $criterionRequiredAttributesCompleteness = new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);

        $criteria = [
            'product_fef37e64_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributesCompleteness,
                ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2'),
                CriterionEvaluationStatus::pending()
            ),
            'product_fef37e64_completeness' => new Write\CriterionEvaluation(
                $criterionRequiredAttributesCompleteness,
                ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2'),
                CriterionEvaluationStatus::pending()
            ),
            'product_df470d52_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributesCompleteness,
                ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'),
                CriterionEvaluationStatus::pending()
            )
        ];

        $criteriaProduct_fef37e64 = (new Write\CriterionEvaluationCollection())
            ->add($criteria['product_fef37e64_non_required_att_completeness'])
            ->add($criteria['product_fef37e64_completeness']);
        $criteriaProduct_df470d52 = (new Write\CriterionEvaluationCollection())
            ->add($criteria['product_df470d52_non_required_att_completeness']);

        $getPendingCriteriaEvaluationsQuery->execute($productIdCollection)->willreturn([
            'fef37e64-a963-47a9-b087-2cc67968f0a2' => $criteriaProduct_fef37e64,
            'df470d52-7723-4890-85a0-e79be625e2ed' => $criteriaProduct_df470d52
        ]);

        $productValues_fef37e64 = $this->givenRandomProductValues();
        $productValues_df470d52 = $this->givenRandomProductValues();

        $productId_fef37e64->__toString()->willReturn('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productIdB->__toString()->willReturn('df470d52-7723-4890-85a0-e79be625e2ed');

        $idFactory->create('fef37e64-a963-47a9-b087-2cc67968f0a2')->willReturn($productId_fef37e64);
        $idFactory->create('df470d52-7723-4890-85a0-e79be625e2ed')->willReturn($productIdB);

        $getEvaluableProductValuesQuery->byProductId($productId_fef37e64)->willReturn($productValues_fef37e64);
        $getEvaluableProductValuesQuery->byProductId($productIdB)->willReturn($productValues_df470d52);

        $evaluationRegistry->get($criterionNonRequiredAttributesCompleteness)->willReturn($evaluateNonRequiredAttributeCompleteness);
        $evaluationRegistry->get($criterionRequiredAttributesCompleteness)->willReturn($evaluateCompleteness);

        $evaluateNonRequiredAttributeCompleteness->evaluate($criteria['product_fef37e64_non_required_att_completeness'], $productValues_fef37e64)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateNonRequiredAttributeCompleteness->evaluate($criteria['product_df470d52_non_required_att_completeness'], $productValues_df470d52)
            ->willReturn(new Write\CriterionEvaluationResult());
        $evaluateCompleteness->evaluate($criteria['product_fef37e64_completeness'], $productValues_fef37e64)
            ->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::any())->shouldBeCalledTimes(2);

        $this->evaluateAllCriteria($productIdCollection);

        foreach ($criteria as $criterionEvaluation) {
            Assert::eq(CriterionEvaluationStatus::done(), $criterionEvaluation->getStatus());
        }
    }

    public function it_continues_to_evaluate_if_an_evaluation_failed(
        CriterionEvaluationRepositoryInterface                 $repository,
        CriteriaEvaluationRegistry                             $evaluationRegistry,
        GetPendingCriteriaEvaluationsByEntityIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface                $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface                             $evaluateCriterion,
        ProductEntityIdFactoryInterface                        $idFactory,
        ProductEntityIdCollection                              $productIdCollection,
        ProductEntityIdInterface                               $productIdA,
        ProductEntityIdInterface                               $productIdB
    )
    {
        $productIdCollection->isEmpty()->willReturn(false);
        $productIdCollection->toArrayString()->willReturn(['42', '123']);

        $idFactory->create('42')->willReturn($productIdA);
        $idFactory->create('123')->willReturn($productIdB);

        $productIdA->__toString()->willReturn('42');
        $productIdB->__toString()->willReturn('123');

        $criterionCode = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);

        $criterionA = new Write\CriterionEvaluation(
            $criterionCode,
            $productIdA->getWrappedObject(),
            CriterionEvaluationStatus::pending()
        );

        $criterionB = new Write\CriterionEvaluation(
            $criterionCode,
            $productIdB->getWrappedObject(),
            CriterionEvaluationStatus::pending()
        );

        $getPendingCriteriaEvaluationsQuery->execute($productIdCollection)->willreturn([
            42 => (new Write\CriterionEvaluationCollection())->add($criterionA),
            123 => (new Write\CriterionEvaluationCollection())->add($criterionB),
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId($productIdA)->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId($productIdB)->willReturn($product123Values);

        $evaluationRegistry->get($criterionCode)->willReturn($evaluateCriterion);
        $evaluateCriterion->evaluate($criterionA, $product42Values)->willThrow(new \Exception('Evaluation failed'));
        $evaluateCriterion->evaluate($criterionB, $product123Values)->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::any())->shouldBeCalledTimes(2);

        $this->evaluateAllCriteria($productIdCollection);

        Assert::eq(CriterionEvaluationStatus::error(), $criterionA->getStatus());
        Assert::eq(CriterionEvaluationStatus::done(), $criterionB->getStatus());
    }

    public function it_evaluates_synchronous_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface                 $repository,
        CriteriaEvaluationRegistry                             $evaluationRegistry,
        GetPendingCriteriaEvaluationsByEntityIdsQueryInterface $getPendingCriteriaEvaluationsQuery,
        GetEvaluableProductValuesQueryInterface                $getEvaluableProductValuesQuery,
        EvaluateCriterionInterface                             $evaluateSpelling,
        SynchronousCriterionEvaluationsFilterInterface         $synchronousCriterionEvaluationsFilter,
        ProductEntityIdFactoryInterface                        $idFactory,
        ProductEntityIdCollection                              $productIdCollection,
        ProductEntityIdInterface                               $productIdA,
        ProductEntityIdInterface                               $productIdB
    )
    {
        $productIdCollection->isEmpty()->willReturn(false);
        $productIdCollection->toArrayString()->willReturn(['42', '123']);

        $idFactory->create('42')->willReturn($productIdA);
        $idFactory->create('123')->willReturn($productIdB);

        $productIdA->__toString()->willReturn('42');
        $productIdB->__toString()->willReturn('123');

        $criterionNonRequiredAttributeCompleteness = new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);

        $criteria = [
            'product_42_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributeCompleteness,
                $productIdA->getWrappedObject(),
                CriterionEvaluationStatus::pending()
            ),
            'product_123_non_required_att_completeness' => new Write\CriterionEvaluation(
                $criterionNonRequiredAttributeCompleteness,
                $productIdB->getWrappedObject(),
                CriterionEvaluationStatus::pending()
            )
        ];

        $product42CriteriaCollection = (new Write\CriterionEvaluationCollection())->add($criteria['product_42_non_required_att_completeness']);
        $product123CriteriaCollection = (new Write\CriterionEvaluationCollection())->add($criteria['product_123_non_required_att_completeness']);
        $getPendingCriteriaEvaluationsQuery->execute($productIdCollection)->willreturn([
            42 => $product42CriteriaCollection,
            123 => $product123CriteriaCollection
        ]);

        $product42Values = $this->givenRandomProductValues();
        $product123Values = $this->givenRandomProductValues();

        $getEvaluableProductValuesQuery->byProductId($productIdA)->willReturn($product42Values);
        $getEvaluableProductValuesQuery->byProductId($productIdB)->willReturn($product123Values);

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

        $this->evaluateSynchronousCriteria($productIdCollection);

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
