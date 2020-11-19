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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\MultipleTextsChecker;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
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

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var SupportedLocaleValidator */
    private $supportedLocaleValidator;

    /** @var FilterProductValuesForSpelling */
    private $filterProductValuesForSpelling;

    /** @var LoggerInterface */
    private $logger;

    /** @var MultipleTextsChecker */
    private $checker;

    public function __construct(
        MultipleTextsChecker $checker,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleValidator $supportedLocaleValidator,
        FilterProductValuesForSpelling $filterProductValuesForSpelling,
        LoggerInterface $logger
    ) {
        $this->checker = $checker;
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
        $this->logger = $logger;
        $this->filterProductValuesForSpelling = $filterProductValuesForSpelling;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localesCodes) {
            foreach ($localesCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $productValues);
            }
        }

        return $evaluationResult;
    }

    private function evaluateChannelLocaleRate(
        Write\CriterionEvaluationResult $evaluationResult,
        ChannelCode $channelCode,
        LocaleCode $localeCode,
        ProductValuesCollection $productValues
    ): void {
        if (!$this->supportedLocaleValidator->isSupported($localeCode)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        try {
            $values = $this->filterProductValuesForSpelling->getFilteredProductValues($productValues);

            $attributesRates = $this->evaluateAttributesRates($channelCode, $localeCode, $values);
        } catch (TextCheckFailedException $exception) {
            $this->logger->error('An error occurred during spelling evaluation', ['error_code' => 'error_during_spelling_evaluation', 'error_message' => $exception->getMessage()]);
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::error());
            return;
        }

        if (empty($attributesRates)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $attributesRates = $this->getRateByAttributes($attributesRates);
        $rate = $this->calculateChannelLocaleRate($attributesRates);

        $evaluationResult
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRate($channelCode, $localeCode, $rate)
            ->addRateByAttributes($channelCode, $localeCode, $attributesRates)
        ;
    }

    /**
     * @throws TextCheckFailedException
     */
    private function evaluateAttributesRates(ChannelCode $channelCode, LocaleCode $localeCode, array $productValues): array
    {
        list('values' => $values, 'weights' => $weights) = $this->prepareSpellCheck($productValues, $channelCode, $localeCode);

        if (empty($values)) {
            return [];
        }

        $this->logger->info('spelling evaluation', [
            'source' => 'evaluation',
            'attributes' => array_keys($values),
            'localeCode' => strval($localeCode),
            'channelCode' => strval($channelCode)
        ]);

        $results = $this->checker->check($values, $localeCode);

        return $this->computeSpellcheckResult($results, $weights);
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

        if (
            preg_match_all("/[\S']+/", $value) === 1 &&
            preg_match('~(@|^\d+|\d+[_\-]|[_\-]\d+)~', $value) === 1
        ) {
            return false;
        }

        return true;
    }

    private function computeProductValueRate(TextCheckResultCollection $checkTextResult, int $faultWeight): Rate
    {
        $rate = 100 - count($checkTextResult) * $faultWeight;

        return new Rate(max(0, $rate));
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): Rate
    {
        return new Rate((int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN));
    }

    private function getRateByAttributes(array $attributesRates): array
    {
        return array_map(function (Rate $attributeRate) {
            return $attributeRate->toInt();
        }, $attributesRates);
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

            $values[strval($attributeCode)] = $productValue;
            $weights[strval($attributeCode)] = $this->getFaultWeight($attribute);
        }

        return [
            'values' => $values,
            'weights' => $weights,
        ];
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
}
