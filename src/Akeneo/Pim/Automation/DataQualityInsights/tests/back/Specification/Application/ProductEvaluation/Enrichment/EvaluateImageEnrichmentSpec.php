<?php


namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompleteness;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\CatalogProviderTrait;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\EvaluationProviderTrait;
use PhpSpec\ObjectBehavior;

class EvaluateImageEnrichmentSpec extends ObjectBehavior
{
    use CatalogProviderTrait;
    use EvaluationProviderTrait;

    public function let(CalculateProductCompletenessInterface $completenessCalculator, EvaluateCompleteness $evaluateCompleteness)
    {
        $this->beConstructedWith($completenessCalculator, $evaluateCompleteness);
    }

    public function it_evaluates_the_image_enrichment(
        CalculateProductCompletenessInterface $completenessCalculator,
        EvaluateCompleteness $evaluateCompleteness
    ): void
    {
        $expectedResult = $this->aWriteCriterionEvaluationResult();
        $criterionEvaluation = $this->aWriteCriterionEvaluation(EvaluateImageEnrichment::CRITERION_CODE, CriterionEvaluationStatus::DONE);
        $attribute = $this->anAttribute();
        $productValues = $this->aListOfProductValues($attribute);

        $evaluateCompleteness->evaluate($completenessCalculator, $criterionEvaluation)->willReturn($expectedResult);

        $this->evaluate($criterionEvaluation, $productValues)->shouldBe($expectedResult);
    }
}
