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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\HashText;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\MultipleTextsChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary\GetDictionaryLastUpdateDateByLocaleQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriterionEvaluationByProductIdAndCriterionCodeQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Psr\Log\LoggerInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class EvaluateSpelling implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'consistency_spelling';

    public const CRITERION_COEFFICIENT = 2;

    private const TEXT_FAULT_WEIGHT = 24;
    private const TEXTAREA_FAULT_WEIGHT = 12;

    public function __construct(
        private MultipleTextsChecker                                            $checker,
        private GetLocalesByChannelQueryInterface                               $localesByChannelQuery,
        private GetCriterionEvaluationByProductIdAndCriterionCodeQueryInterface $getCriterionEvaluationResultQuery,
        private SupportedLocaleValidator                                        $supportedLocaleValidator,
        private FilterProductValuesForSpelling                                  $filterProductValuesForSpelling,
        private LoggerInterface                                                 $logger,
        private HashText                                                        $hashText,
        private GetDictionaryLastUpdateDateByLocaleQueryInterface               $getDictionaryLastUpdateDateByLocaleQuery
    ) {
    }

    public function evaluate(CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): CriterionEvaluationResult
    {
        $previousEvaluation = $this->getCriterionEvaluationResultQuery->execute(
            $criterionEvaluation->getEntityId(),
            $this->getCode()
        );

        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();

        $evaluationResult = new CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localesCodes) {
            foreach ($localesCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $productValues, $previousEvaluation);
            }
        }

        return $evaluationResult;
    }

    private function evaluateChannelLocaleRate(
        CriterionEvaluationResult $evaluationResult,
        ChannelCode                     $channelCode,
        LocaleCode                      $localeCode,
        ProductValuesCollection         $productValues,
        ?Read\CriterionEvaluation       $previousEvaluationResult,
    ): void {
        if (!$this->supportedLocaleValidator->isSupported($localeCode)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $valuesToCheck = $this->filterProductValuesForSpelling->getFilteredProductValues($productValues);
        list('values' => $valuesToCheck, 'weights' => $weights) = $this->prepareSpellCheck($valuesToCheck, $channelCode, $localeCode);

        if (empty($valuesToCheck)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $hashedValues = [];
        $unchangedAttributeRates = [];
        $dictionaryLastUpdateDate = $this->getDictionaryLastUpdateDateByLocaleQuery->execute($localeCode);

        if ($previousEvaluationResult !== null && $dictionaryLastUpdateDate !== null) {
            $dateComparisonResult = $previousEvaluationResult->getEvaluatedAt() > $dictionaryLastUpdateDate;
        } else {
            $dateComparisonResult = true;
        }

        foreach ($valuesToCheck as $attributeCode => $value) {
            $hashedValue = $this->hashText->hash($value);
            $hashedValues[$attributeCode] = $hashedValue;
            $previousHashedValue =
                $previousEvaluationResult
                    ?->getResult()
                    ?->getData()['hashed_values'][(string)$channelCode][(string)$localeCode][$attributeCode]
                ?? null;
            $previousRate =
                $previousEvaluationResult
                    ?->getResult()
                    ?->getData()['attributes_with_rates'][(string)$channelCode][(string)$localeCode][$attributeCode]
                ?? null;

            if ($dateComparisonResult && null !== $previousRate && $previousHashedValue === $hashedValue) {
                $unchangedAttributeRates[$attributeCode] = $previousRate;
                unset($valuesToCheck[$attributeCode]);
            }
        }

        try {
            $spellcheckResults = $this->checker->check($valuesToCheck, $localeCode);
        } catch (TextCheckFailedException $exception) {
            $this->logTextCheckError($exception, $channelCode, $localeCode, $valuesToCheck);
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::error());
            return;
        }

        $attributesRates = array_merge(
            $this->computeSpellcheckResult($spellcheckResults, $weights),
            $unchangedAttributeRates
        );

        $rate = $this->calculateChannelLocaleRate($attributesRates);

        $evaluationResult
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRate($channelCode, $localeCode, $rate)
            ->addRateByAttributes($channelCode, $localeCode, $attributesRates)
            ->addData('hashed_values', $channelCode, $localeCode, $hashedValues);
    }


    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    public function getCoefficient(): int
    {
        return self::CRITERION_COEFFICIENT;
    }

    private function isValidValue($value): bool
    {
        if ($value === null || !is_string($value) || is_numeric($value)) {
            return false;
        }

        $value = trim($value);

        if (empty($value)) {
            return false;
        }

        // PIM-9975: A copy-paste from Word brings a complex text with a lot of tags. The HTML filter is long
        // and can cause a timeout and a 500 error. So we decided to skip the evaluation.
        if ($this->isTextComingFromWord($value)) {
            return false;
        }

        if (
            preg_match_all("/[\S']+/", $value) === 1 &&
            preg_match('~(@|^\d+|\d+[_\-]|[_\-]\d+)~', $value) === 1
        ) {
            return false;
        }

        return true;
    }

    private function computeProductValueRate(TextCheckResultCollection $checkTextResult, int $faultWeight): int
    {
        $rate = 100 - count($checkTextResult) * $faultWeight;

        return max(0, $rate);
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): Rate
    {
        return new Rate((int)round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN));
    }

    private function prepareSpellCheck(array $productValues, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $values = [];
        $weights = [];

        foreach ($productValues as $productValueByChannelAndLocale) {
            $attribute = $productValueByChannelAndLocale->getAttribute();
            $attributeCode = $attribute->getCode();
            $productValue = $productValueByChannelAndLocale->getValueByChannelAndLocale($channelCode, $localeCode);

            if (!$this->isValidValue($productValue)) {
                continue;
            }

            $values[strval($attributeCode)] = $this->cleanHtmlTags($productValue);
            $weights[strval($attributeCode)] = $this->getFaultWeight($attribute);
        }

        return [
            'values' => $values,
            'weights' => $weights,
        ];
    }

    private function cleanHtmlTags(string $value): string
    {
        /**
         * We don't use strip_tags or Aspell HTML mode to avoid truncating the text when there's a non escaped HTML special char
         * cf PLG-672
         */
        $cleanedValue = preg_replace('/<[^<]+?>/', '', $value);

        return \is_string($cleanedValue) ? $cleanedValue : $value;
    }

    private function computeSpellcheckResult(array $results, array $weights): array
    {
        $attributesRates = [];
        foreach ($results as $attributeCode => $result) {
            $faultWeight = $weights[$attributeCode];

            $rate = $this->computeProductValueRate($result, $faultWeight);
            $attributesRates[$attributeCode] = $rate;
        }
        return $attributesRates;
    }

    private function getFaultWeight(Attribute $attribute): int
    {
        if ($attribute->getType()->equals(AttributeType::textarea())) {
            return self::TEXTAREA_FAULT_WEIGHT;
        }

        return self::TEXT_FAULT_WEIGHT;
    }

    private function isTextComingFromWord(string $text): bool
    {
        return strpos($text, '<w:WordDocument>') !== false;
    }

    private function logTextCheckError(TextCheckFailedException $exception, ChannelCode $channelCode, LocaleCode $localeCode, array $productValues): void
    {
        $this->logger->error('An error occurred during spelling evaluation', [
            'error_code' => 'error_during_spelling_evaluation',
            'exception' => $exception->getPrevious() ?? $exception,
            'channel' => (string)$channelCode,
            'locale' => (string)$localeCode,
            'product_values' => $productValues,
        ]);
    }
}
