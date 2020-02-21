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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class EvaluateCompletenessOfNonRequiredAttributes implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'completeness_of_non_required_attributes';

    /** @var CriterionCode */
    private $code;

    /** @var CalculateProductCompletenessInterface */
    private $completenessCalculator;

    /** @var EvaluateCompleteness */
    private $evaluateCompleteness;

    public function __construct(CalculateProductCompletenessInterface $completenessCalculator, EvaluateCompleteness $evaluateCompleteness)
    {
        $this->code = new CriterionCode(self::CRITERION_CODE);
        $this->completenessCalculator = $completenessCalculator;
        $this->evaluateCompleteness = $evaluateCompleteness;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation): Write\CriterionEvaluationResult
    {
        return $this->evaluateCompleteness->evaluate($this->completenessCalculator, $criterionEvaluation);
    }

    public function getCode(): CriterionCode
    {
        return $this->code;
    }
}
