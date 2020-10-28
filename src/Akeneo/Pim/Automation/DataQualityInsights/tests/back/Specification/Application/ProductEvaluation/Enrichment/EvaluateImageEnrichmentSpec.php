<?php


namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\CatalogProvider;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\EvaluationProvider;
use Akeneo\Pim\Structure\Component\AttributeTypes;
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
        $criterionEvaluation = EvaluationProvider::aWritableCriterionEvaluation(
            EvaluateImageEnrichment::CRITERION_CODE,
            CriterionEvaluationStatus::DONE,
            $productId->toInt()
        );
        $productValues = CatalogProvider::aListOfProductValues();
        $channelsWithLocales = CatalogProvider::aListOfChannelsWithLocales();
        $completenessResult = EvaluationProvider::aWritableCompletenessCalculationResult([
            'a_channel' => [
                'en_US' => ['rate' => 100, 'attributes' => []],
                'fr_FR' => ['rate' => 100, 'attributes' => []],
                'de_DE' => ['rate' => 100, 'attributes' => []],
            ]
        ]);

        $expectedResult = EvaluationProvider::aWritableCriterionEvaluationResult([
            'a_channel' => [
                'en_US' => [
                    'rate' => 100,
                    'attributes' => [],
                    'status' => 'done',
                ],
                'fr_FR' => [
                    'rate' => 100,
                    'attributes' => [],
                    'status' => 'done',
                ],
                'de_DE' => [
                    'rate' => 100,
                    'attributes' => [],
                    'status' => 'done',
                ],
            ]
        ]);

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn($channelsWithLocales);
        $completenessCalculator->calculate($productId)->willReturn($completenessResult);

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedResult);
    }
}
