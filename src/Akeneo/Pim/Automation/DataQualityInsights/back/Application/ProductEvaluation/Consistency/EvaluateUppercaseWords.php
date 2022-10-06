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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords\ComputeCaseWordsRate;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class EvaluateUppercaseWords implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'consistency_textarea_uppercase_words';

    public const CRITERION_COEFFICIENT = 1;

    public function __construct(
        private EvaluateCaseWords $evaluateCaseWords,
        private ComputeCaseWordsRate $computeCaseWordsRate
    ) {
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    public function getCoefficient(): int
    {
        return self::CRITERION_COEFFICIENT;
    }

    public function evaluate(CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): CriterionEvaluationResult
    {
        return ($this->evaluateCaseWords)($criterionEvaluation, $productValues, $this->computeCaseWordsRate);
    }
}
