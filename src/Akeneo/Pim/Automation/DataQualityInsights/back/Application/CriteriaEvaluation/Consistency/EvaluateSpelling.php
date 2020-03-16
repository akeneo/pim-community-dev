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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\TextCheckFailedException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
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
    const CRITERION_CODE = 'consistency_spelling';

    const TEXT_FAULT_WEIGHT = 24;
    const TEXTAREA_FAULT_WEIGHT = 12;

    /** @var TextChecker */
    private $textChecker;

    /** @var GetLocalesByChannelQueryInterface */
    private $localesByChannelQuery;

    /** @var SupportedLocaleChecker */
    private $supportedLocaleChecker;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TextChecker $textChecker,
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        SupportedLocaleChecker $supportedLocaleChecker,
        LoggerInterface $logger
    ) {
        $this->textChecker = $textChecker;
        $this->localesByChannelQuery = $localesByChannelQuery;
        $this->supportedLocaleChecker = $supportedLocaleChecker;
        $this->logger = $logger;
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
        if (!$this->supportedLocaleChecker->isSupported($localeCode)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        try {
            $textRates = $this->evaluateAttributesRates($channelCode, $localeCode, $productValues->getLocalizableTextValues(), self::TEXT_FAULT_WEIGHT);
            $textareaRates = $this->evaluateAttributesRates($channelCode, $localeCode, $productValues->getLocalizableTextareaValues(), self::TEXTAREA_FAULT_WEIGHT);
        } catch (TextCheckFailedException $exception) {
            $this->logger->error('An error occurred during spelling evaluation', ['error_code' => 'error_during_spelling_evaluation', 'error_message' => $exception->getMessage()]);
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::error());
            return;
        }

        $attributesRates = array_merge($textRates, $textareaRates);

        if (empty($attributesRates)) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $rate = $this->calculateChannelLocaleRate($attributesRates);
        $improvableAttributes = $this->computeImprovableAttributes($attributesRates);

        $evaluationResult
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRate($channelCode, $localeCode, $rate)
            ->addImprovableAttributes($channelCode, $localeCode, $improvableAttributes);
    }

    private function evaluateAttributesRates(ChannelCode $channelCode, LocaleCode $localeCode, \Iterator $productValues, int $faultWeight): array
    {
        $attributesRates = [];
        foreach ($productValues as $productValueByChannelAndLocale) {
            $attributeCode = $productValueByChannelAndLocale->getAttribute()->getCode();
            $productValue = $productValueByChannelAndLocale->getValueByChannelAndLocale($channelCode, $localeCode);

            if ($productValue === null || $productValue === '') {
                continue;
            }

            $this->logger->info('spelling evaluation', [
                'source' => 'evaluation',
                'value' => $productValue,
                'localeCode' => strval($localeCode),
                'channelCode' => strval($channelCode)
            ]);

            $textCheckResult = $this->textChecker->check(strval($productValue), $localeCode);
            $rate = $this->computeProductValueRate($textCheckResult, $faultWeight);
            $attributesRates[strval($attributeCode)] = $rate;
        }

        return $attributesRates;
    }

    public function getCode(): CriterionCode
    {
        return new CriterionCode(self::CRITERION_CODE);
    }

    private function computeProductValueRate(TextCheckResultCollection $checkTextResult, int $faultWeight): Rate
    {
        $rate = 100 - count($checkTextResult) * $faultWeight;

        return new Rate(max(0, $rate));
    }

    private function calculateChannelLocaleRate(array $channelLocaleRates): Rate
    {
        $channelLocaleRates = array_map(function (Rate $rate) {
            return $rate->toInt();
        }, $channelLocaleRates);

        return new Rate((int) round(array_sum($channelLocaleRates) / count($channelLocaleRates), 0, PHP_ROUND_HALF_DOWN));
    }

    private function computeImprovableAttributes(array $attributesRates): array
    {
        return array_keys(array_filter($attributesRates, function (Rate $attributeRate) {
            return !$attributeRate->isPerfect();
        }));
    }
}
