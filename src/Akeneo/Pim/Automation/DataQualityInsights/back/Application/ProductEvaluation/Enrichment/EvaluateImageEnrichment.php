<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

class EvaluateImageEnrichment implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'missing_image_attribute';

    private CalculateProductCompletenessInterface $completenessCalculator;

    private EvaluateCompleteness $evaluateCompleteness;

    private CriterionCode $code;

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
}
