<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

/**
 * For performance and data size reasons, the results of this criterion are evaluated and stored by family for the products.
 */
final class EvaluateProductAttributeSpelling implements EvaluateCriterionInterface
{
    public function evaluate(CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): CriterionEvaluationResult
    {
        return new CriterionEvaluationResult();
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE);
    }

    public function getCoefficient(): int
    {
        return EvaluateAttributeSpelling::CRITERION_COEFFICIENT;
    }
}
