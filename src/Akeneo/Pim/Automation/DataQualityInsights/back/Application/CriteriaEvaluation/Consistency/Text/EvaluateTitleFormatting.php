<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductTitle;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class EvaluateTitleFormatting implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'consistency_text_title_formatting';

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var BuildProductValuesInterface */
    private $buildProductValues;

    /** @var TitleFormattingServiceInterface */
    private $titleFormattingService;

    public function __construct(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        BuildProductValuesInterface $buildProductValues,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->buildProductValues = $buildProductValues;
        $this->titleFormattingService = $titleFormattingService;
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation): CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->execute();
        $productValues = $this->buildProductValues->buildTitleValues($criterionEvaluation->getProductId());
        $ratesAndSuggestionByChannelAndLocale = $this->computeAttributeRates($localesByChannel, $productValues);

        $rates = $this->buildCriterionRateCollection($ratesAndSuggestionByChannelAndLocale);
        $attributesCodesToImprove = $this->computeAttributeCodesToImprove($ratesAndSuggestionByChannelAndLocale);
        $suggestionsByChannelAndLocale = $this->extractSuggestions($ratesAndSuggestionByChannelAndLocale);

        return new CriterionEvaluationResult($rates, [
            'attributes' => $attributesCodesToImprove,
            'suggestions' => $suggestionsByChannelAndLocale
        ]);
    }

    private function computeAttributeRates(array $localesByChannel, array $productValues): array
    {
        $ratesAndSuggestionByChannelAndLocale = [];
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                if (false === $this->isSupportedLocale($localeCode)) {
                    continue;
                }

                foreach ($productValues as $attributeCode => $productValueByChannelAndLocale) {
                    $productValue = $productValueByChannelAndLocale[$channelCode][$localeCode];
                    $rateAndSuggestion = $this->computeProductValueRate($productValue);
                    if (empty($rateAndSuggestion)) {
                        continue;
                    }
                    $ratesAndSuggestionByChannelAndLocale[$channelCode][$localeCode][$attributeCode] = $rateAndSuggestion;
                }
            }
        }
        return $ratesAndSuggestionByChannelAndLocale;
    }

    private function isSupportedLocale(string $localeCode)
    {
        preg_match('~en_[A-Z]{2}$~', $localeCode, $matches);
        return count($matches) > 0;
    }

    private function computeProductValueRate(?string $originalTitle): array
    {
        if ($originalTitle === null) {
            return [];
        }

        try {
            $titleSuggestion = $this->titleFormattingService->format(new ProductTitle($originalTitle));
        } catch (\Exception $e) {
            return [];
        }

        $numberOfDifferences = $this->computeDifference($originalTitle, $titleSuggestion->__toString());

        $rate = 100 - ($numberOfDifferences * 12);
        if ($rate < 0) {
            $rate = 0;
        }

        return [
            'rate' => $rate,
            'titleSuggestion' => $titleSuggestion->__toString()
        ];
    }

    private function explodeStringByWords(string $title): array
    {
        $aWordPerLine = wordwrap($title, 1);
        return explode(PHP_EOL, $aWordPerLine);
    }

    private function computeDifference(string $originalTitle, string $suggestedTitle): int
    {
        $titleSuggestionArrayOfWords = $this->explodeStringByWords($suggestedTitle);
        $originalTitleArrayOfWords = $this->explodeStringByWords($originalTitle);

        $intersection = array_intersect($originalTitleArrayOfWords, $titleSuggestionArrayOfWords);

        return count($originalTitleArrayOfWords) - count($intersection);
    }

    private function buildCriterionRateCollection(array $ratesByChannelAndLocale): CriterionRateCollection
    {
        $rates = new CriterionRateCollection();
        foreach ($ratesByChannelAndLocale as $channelCode => $ratesByLocale) {
            foreach ($ratesByLocale as $localeCode => $ratesByAttribute) {
                $rates->addRate(new ChannelCode($channelCode), new LocaleCode($localeCode), new Rate(current($ratesByAttribute)['rate']));
            }
        }
        return $rates;
    }

    private function computeAttributeCodesToImprove(array $ratesByChannelAndLocale): array
    {
        $attributesCodesToImprove = [];
        foreach ($ratesByChannelAndLocale as $channelCode => $ratesByLocale) {
            foreach ($ratesByLocale as $localeCode => $ratesAndSuggestionByAttribute) {
                foreach ($ratesAndSuggestionByAttribute as $attributeCode => $rateAndSuggestion) {
                    if (! empty($rateAndSuggestion['rate'] < 100)) {
                        $attributesCodesToImprove[$channelCode][$localeCode] = [$attributeCode];
                    }
                }
            }
        }
        return $attributesCodesToImprove;
    }

    private function extractSuggestions(array $ratesAndSuggestionByChannelAndLocale)
    {
        $suggestionsByChannelAndLocale = [];
        foreach ($ratesAndSuggestionByChannelAndLocale as $channelCode => $rateAndSuggestionsByLocale) {
            foreach ($rateAndSuggestionsByLocale as $localeCode => $rateAndSuggestionByAttribute) {
                $rateAndSuggestion = current($rateAndSuggestionByAttribute);
                if ($rateAndSuggestion['rate'] === 100) {
                    continue;
                }
                $suggestionsByChannelAndLocale[$channelCode][$localeCode] = $rateAndSuggestion['titleSuggestion'];
            }
        }
        return $suggestionsByChannelAndLocale;
    }
}
