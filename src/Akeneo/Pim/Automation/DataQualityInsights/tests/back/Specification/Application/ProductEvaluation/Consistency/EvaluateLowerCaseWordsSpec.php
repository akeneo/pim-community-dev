<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords\ComputeCaseWordsRate;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateCaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;

final class EvaluateLowerCaseWordsSpec extends ObjectBehavior
{
    public function let(EvaluateCaseWords $evaluateCaseWords, ComputeCaseWordsRate $computeCaseWordsRate)
    {
        $this->beConstructedWith($evaluateCaseWords, $computeCaseWordsRate);
    }

    public function it_calls_evaluate_method_with_lower_case_compute_for_criterion_and_product_values(
        EvaluateCaseWords $evaluateCaseWords, ComputeCaseWordsRate $computeCaseWordsRate
    ) {
        $criterionEvaluation1 = new CriterionEvaluation(
            new CriterionCode('criterion1'),
            ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')),
            CriterionEvaluationStatus::pending()
        );

        $productValues1 = (new ProductValuesCollection());
        $expectedResult = (new CriterionEvaluationResult());

        $evaluateCaseWords->__invoke($criterionEvaluation1, $productValues1, $computeCaseWordsRate)->willReturn($expectedResult);

        $this->evaluate($criterionEvaluation1, $productValues1)->shouldBeLike($expectedResult);
    }
}
