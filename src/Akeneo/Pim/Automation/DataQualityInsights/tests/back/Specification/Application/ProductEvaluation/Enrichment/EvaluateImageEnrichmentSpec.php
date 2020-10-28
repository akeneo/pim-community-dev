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

    public function it_evaluates_the_image_enrichment_for_a_product_with_image (
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
        $imageAttribute = CatalogProvider::anAttribute('an_image_attribute', AttributeTypes::IMAGE);
        $secondImageAttribute = CatalogProvider::anAttribute('a_second_image_attribute', AttributeTypes::IMAGE);
        $textAttribute = CatalogProvider::anAttribute('a_text_attribute');
        $productValues = CatalogProvider::aListOfProductValues([
            ['attribute' => $imageAttribute, 'values' => ['a_channel' => ['en_US' => '/an_en_image.jpg', 'fr_FR' => '/an_fr_image.jpg', 'de_DE' => '']]],
            ['attribute' => $secondImageAttribute, 'values' => ['a_channel' => ['en_US' => '/an_en_image.jpg', 'fr_FR' => '', 'de_DE' => '']]],
            ['attribute' => $textAttribute, 'values' => ['a_channel' => ['en_US' => '', 'fr_FR' => '', 'de_DE' => '']]],
        ]);
        $channelsWithLocales = CatalogProvider::aListOfChannelsWithLocales([
            'a_channel' => ['en_US', 'fr_FR', 'de_DE']
        ]);
        $completenessResult = EvaluationProvider::aWritableCompletenessCalculationResult([
            'a_channel' => [
                'en_US' => ['rate' => 100, 'attributes' => []],
                'fr_FR' => ['rate' => 50, 'attributes' => ['a_second_image']],
                'de_DE' => ['rate' => 0, 'attributes' => ['an_image', 'a_second_image']],
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
                    'attributes' => ['a_second_image' => 0],
                    'status' => 'done',
                ],
                'de_DE' => [
                    'rate' => 0,
                    'attributes' => ['an_image' => 0, 'a_second_image' => 0],
                    'status' => 'done',
                ],
            ]
        ]);

        $localesByChannelQuery->getChannelLocaleCollection()->willReturn($channelsWithLocales);
        $completenessCalculator->calculate($productId)->willReturn($completenessResult);

        $this->evaluate($criterionEvaluation, $productValues)->shouldBeLike($expectedResult);
    }

    public function it_evaluates_the_image_enrichment_for_a_product_without_image (
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
       $textAttribute = CatalogProvider::anAttribute('a_text_attribute');
        $productValues = CatalogProvider::aListOfProductValues([
            ['attribute' => $textAttribute, 'values' => ['a_channel' => ['en_US' => '', 'fr_FR' => '']]],
        ]);
        $channelsWithLocales = CatalogProvider::aListOfChannelsWithLocales([
            'a_channel' => ['en_US', 'fr_FR']
        ]);
        $completenessResult = EvaluationProvider::aWritableCompletenessCalculationResult([
            'a_channel' => [
                'en_US' => ['rate' => null],
                'fr_FR' => ['rate' => 0, 'attributes' => []],
            ]
        ]);

        $expectedResult = EvaluationProvider::aWritableCriterionEvaluationResult([
            'a_channel' => [
                'en_US' => [
                    'status' => 'not_applicable',
                ],
                'fr_FR' => [
                    'rate' => 0,
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
