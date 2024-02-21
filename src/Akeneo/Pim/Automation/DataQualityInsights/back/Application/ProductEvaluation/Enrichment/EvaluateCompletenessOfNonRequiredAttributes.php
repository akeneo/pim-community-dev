<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateCompletenessOfNonRequiredAttributes implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'completeness_of_non_required_attributes';

    public const CRITERION_COEFFICIENT = 1;

    private CriterionCode $code;

    private CalculateProductCompletenessInterface $completenessCalculator;

    private EvaluateCompleteness $evaluateCompleteness;

    public function __construct(CalculateProductCompletenessInterface $completenessCalculator, EvaluateCompleteness $evaluateCompleteness)
    {
        $this->code = new CriterionCode(self::CRITERION_CODE);
        $this->completenessCalculator = $completenessCalculator;
        $this->evaluateCompleteness = $evaluateCompleteness;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        return $this->evaluateCompleteness->evaluate($this->completenessCalculator, $criterionEvaluation);
    }

    public function getCode(): CriterionCode
    {
        return $this->code;
    }

    public function getCoefficient(): int
    {
        return self::CRITERION_COEFFICIENT;
    }
}
