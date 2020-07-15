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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\CriterionNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class EvaluatePendingCriteriaSpec extends ObjectBehavior
{
    public function let(CriterionEvaluationRepositoryInterface $repository, CriteriaEvaluationRegistry $registry, LoggerInterface $logger)
    {
        $this->beConstructedWith($repository, $registry, $logger);
    }

    public function it_evaluates_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $registry,
        EvaluateCriterionInterface $evaluateCriterion
    ) {
        $criterionCode = new CriterionCode('completeness');

        $criterionA = new Write\CriterionEvaluation(
            new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
            $criterionCode,
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $criterionB = new Write\CriterionEvaluation(
            new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
            $criterionCode,
            new ProductId(123),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $repository->findPendingByProductIds([42, 123])->willreturn([$criterionA, $criterionB]);

        $registry->get($criterionCode)->willReturn($evaluateCriterion);
        $evaluateCriterion->evaluate($criterionA)->willReturn(new Write\CriterionEvaluationResult());
        $evaluateCriterion->evaluate($criterionB)->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isInProgress();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isDone();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionB) {
            return
                $criterionB->getId() === $criterion->getId() && $criterion->getStatus()->isInProgress();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionB) {
            return
                $criterionB->getId() === $criterion->getId() && $criterion->getStatus()->isDone();
        }))->shouldBeCalled();

        $this->evaluateAllCriteria([42, 123]);
    }

    public function it_continues_to_evaluate_if_an_evaluation_failed(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $registry,
        EvaluateCriterionInterface $evaluateCriterion
    ) {
        $criterionCode = new CriterionCode('completeness');

        $criterionA = new Write\CriterionEvaluation(
            new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
            $criterionCode,
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $criterionB = new Write\CriterionEvaluation(
            new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
            $criterionCode,
            new ProductId(123),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $repository->findPendingByProductIds([42, 123])->willreturn([$criterionA, $criterionB]);

        $registry->get($criterionCode)->willReturn($evaluateCriterion);
        $evaluateCriterion->evaluate($criterionA)->willThrow(new \Exception('Evaluation failed'));
        $evaluateCriterion->evaluate($criterionB)->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isInProgress();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isError();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionB) {
            return
                $criterionB->getId() === $criterion->getId() && $criterion->getStatus()->isInProgress();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionB) {
            return
                $criterionB->getId() === $criterion->getId() && $criterion->getStatus()->isDone();
        }))->shouldBeCalled();

        $this->evaluateAllCriteria([42, 123]);
    }

    public function it_evaluates_synchronous_criteria_for_a_set_of_products(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $registry,
        EvaluateCriterionInterface $evaluateCriterion
    ) {
        $criterionACode = new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);
        $criterionBCode = new CriterionCode('asynchronous_criterion');

        $criterionA = new Write\CriterionEvaluation(
            new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
            $criterionACode,
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $criterionB = new Write\CriterionEvaluation(
            new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
            $criterionBCode,
            new ProductId(123),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()

        );

        $repository->findPendingByProductIds([42, 123])->willreturn([$criterionA, $criterionB]);

        $registry->get($criterionACode)->willReturn($evaluateCriterion);
        $evaluateCriterion->evaluate($criterionA)->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isInProgress();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isDone();
        }))->shouldBeCalled();

        $this->evaluateSynchronousCriteria([42, 123]);
    }

    public function it_ignores_unknown_criterion(
        CriterionEvaluationRepositoryInterface $repository,
        CriteriaEvaluationRegistry $registry,
        EvaluateCriterionInterface $evaluateCriterion
    ) {
        $criterionCode = new CriterionCode('completeness');
        $unknownCriterionCode = new CriterionCode('unknown_criterion');

        $criterionA = new Write\CriterionEvaluation(
            new CriterionEvaluationId('95f124de-45cd-495e-ac58-349086ad6cd4'),
            $criterionCode,
            new ProductId(42),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $criterionB = new Write\CriterionEvaluation(
            new CriterionEvaluationId('d7bcae1e-30c9-4626-9c4f-d06cae03e77e'),
            $unknownCriterionCode,
            new ProductId(123),
            new \DateTimeImmutable('2019-10-28 10:41:56'),
            CriterionEvaluationStatus::pending()
        );

        $repository->findPendingByProductIds([42, 123])->willreturn([$criterionA, $criterionB]);

        $registry->get($criterionCode)->willReturn($evaluateCriterion);
        $registry->get($unknownCriterionCode)->willThrow(CriterionNotFoundException::class);
        $evaluateCriterion->evaluate($criterionA)->willReturn(new Write\CriterionEvaluationResult());

        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isInProgress();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionA) {
            return
                $criterionA->getId() === $criterion->getId() && $criterion->getStatus()->isDone();
        }))->shouldBeCalled();
        $repository->update(Argument::that(function ($criterion) use ($criterionB) {
            return
                $criterionB->getId() === $criterion->getId() && $criterion->getStatus()->isError();
        }))->shouldBeCalled();

        $this->evaluateAllCriteria([42, 123]);
    }
}
