<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\FilterProductValuesForTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionApplicabilityInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToProvideATitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductTitle;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class EvaluateTitleFormatting implements EvaluateCriterionInterface, EvaluateCriterionApplicabilityInterface
{
    public const CRITERION_CODE = 'consistency_text_title_formatting';

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var TitleFormattingServiceInterface */
    private $titleFormattingService;

    /** @var GetIgnoredProductTitleSuggestionQueryInterface */
    private $getIgnoredProductTitleSuggestionQuery;

    /** @var FilterProductValuesForTitleFormatting */
    private $filterProductValuesForTitleFormatting;

    public function __construct(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery,
        FilterProductValuesForTitleFormatting $filterProductValuesForTitleFormatting
    ) {
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->titleFormattingService = $titleFormattingService;
        $this->getIgnoredProductTitleSuggestionQuery = $getIgnoredProductTitleSuggestionQuery;
        $this->filterProductValuesForTitleFormatting = $filterProductValuesForTitleFormatting;
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $productMainTitleValues = $this->filterProductValuesForTitleFormatting->getMainTitleValues($productValues);

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($criterionEvaluation->getProductId(), $evaluationResult, $channelCode, $localeCode, $productMainTitleValues);
            }
        }

        return $evaluationResult;
    }

    public function evaluateApplicability(ProductValuesCollection $productValues): Write\CriterionApplicability
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $productMainTitleValues = $this->filterProductValuesForTitleFormatting->getMainTitleValues($productValues);

        $evaluationResult = new Write\CriterionEvaluationResult();
        $isApplicable = false;
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $productValue = null !== $productMainTitleValues ? $productMainTitleValues->getValueByChannelAndLocale($channelCode, $localeCode) : null;
                if (!$this->isSupportedLocale($localeCode) || null === $productValue || '' === $productValue) {
                    $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
                } else {
                    $isApplicable = true;
                }
            }
        }

        return new Write\CriterionApplicability($evaluationResult, $isApplicable);
    }

    private function evaluateChannelLocaleRate(
        ProductId $productId,
        Write\CriterionEvaluationResult $evaluationResult,
        ChannelCode $channelCode,
        LocaleCode $localeCode,
        ?ProductValues $mainTitleValues
    ): void {
        if (null === $mainTitleValues || !$this->isSupportedLocale($localeCode)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $attributeCodeAsMainTitle = $mainTitleValues->getAttribute()->getCode();
        $productValue = $mainTitleValues->getValueByChannelAndLocale($channelCode, $localeCode);

        if (null === $productValue || '' === $productValue) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        try {
            $productValueResult = $this->evaluateProductValue($productId, $productValue, $channelCode, $localeCode);
        } catch (UnableToProvideATitleSuggestion $exception) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::error());
            return;
        }

        $rate = $productValueResult['rate'];
        $evaluationResult->addRate($channelCode, $localeCode, $rate);

        if (isset($productValueResult['titleSuggestion'])) {
            $evaluationResult->addData('suggestions', $channelCode, $localeCode, $productValueResult['titleSuggestion']);
        }

        if (!$rate->isPerfect()) {
            $evaluationResult->addImprovableAttributes($channelCode, $localeCode, [strval($attributeCodeAsMainTitle)]);
        }

        $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done());
    }

    private function isSupportedLocale(LocaleCode $localeCode): bool
    {
        return preg_match('~^en_[A-Z]{2}$~', strval($localeCode)) === 1;
    }

    private function evaluateProductValue(ProductId $productId, ?string $originalTitle, ChannelCode $channel, LocaleCode $locale): array
    {
        $titleSuggestion = $this->titleFormattingService->format(new ProductTitle($originalTitle));

        if ($this->checkTitleSuggestionIsIgnored($titleSuggestion, $productId, strval($channel), strval($locale)) === true) {
            return [
                'rate' => new Rate(100),
            ];
        }

        $numberOfDifferences = $this->computeDifference($originalTitle, strval($titleSuggestion));

        $rate = 100 - ($numberOfDifferences * 12);
        if ($rate < 0) {
            $rate = 0;
        }

        $result = ['rate' => new Rate($rate)];

        if ($rate < 100) {
            $result['titleSuggestion'] = strval($titleSuggestion);
        }

        return $result;
    }

    private function checkTitleSuggestionIsIgnored(ProductTitle $titleSuggestion, ProductId $productId, string $channel, string $locale): bool
    {
        $ignoredTitleSuggestion = $this->getIgnoredProductTitleSuggestionQuery->execute(
            $productId,
            new ChannelCode($channel),
            new LocaleCode($locale)
        );

        return (strval($titleSuggestion) === $ignoredTitleSuggestion);
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
}
