<?php


namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompleteness;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\CatalogProvider;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\EvaluationProvider;
use PhpSpec\ObjectBehavior;

class EvaluateImageEnrichmentSpec extends ObjectBehavior
{
    public function let(CalculateProductCompletenessInterface $completenessCalculator, GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->beConstructedWith($completenessCalculator, $localesByChannelQuery);
    }

    public function it_evaluates_the_image_enrichment(
        CalculateProductCompletenessInterface $completenessCalculator,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ): void
    {
        $productId = new ProductId(1234);
        $criterionEvaluation = EvaluationProvider::aWritableCriterionEvaluation(EvaluateImageEnrichment::CRITERION_CODE, CriterionEvaluationStatus::DONE, $productId->toInt());
        $attribute = CatalogProvider::anAttribute();
        $productValues = CatalogProvider::aListOfProductValues($attribute);
        $channelsWithLocales = CatalogProvider::aListOfChannelsWithLocales();
        $completenessResult = EvaluationProvider::aWritableCompletenessCalculationResult();

        $expectedResult = EvaluationProvider::aWritableCriterionEvaluationResult();

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn($channelsWithLocales);
        $completenessCalculator->calculate($productId)->willReturn($completenessResult);

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedResult);
    }
}
