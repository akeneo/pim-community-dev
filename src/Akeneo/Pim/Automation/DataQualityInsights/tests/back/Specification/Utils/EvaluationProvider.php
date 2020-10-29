<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

abstract class EvaluationProvider
{
    public static function aWritableCriterionEvaluation(string $code = 'a_criterion', string $status = CriterionEvaluationStatus::DONE, int $productId = 1234, Write\CriterionEvaluationResult $result = null): Write\CriterionEvaluation
    {
        $evaluation = new Write\CriterionEvaluation(
            new CriterionCode($code),
            new ProductId($productId),
            new CriterionEvaluationStatus($status)
        );

        if ($result !== null) {
            $evaluation->end($result);
        }

        return $evaluation;
    }

    public static function aWritableCriterionEvaluationResult(array $resultData = ['a_channel' => ['en_US' => ['rate' => 100, 'status' => CriterionEvaluationResultStatus::DONE, 'attributes' => ['an_attribute' => 100]]]]): Write\CriterionEvaluationResult
    {
        $result = new Write\CriterionEvaluationResult();
        foreach ($resultData as $channel => $locales) {
            foreach ($locales as $locale => $data) {
                $channelCode = new ChannelCode($channel);
                $localeCode = new LocaleCode($locale);

                if (array_key_exists('rate', $data)) {
                    $result->addRate($channelCode, $localeCode, new Rate((int) $data['rate']));
                }
                if (array_key_exists('attributes', $data)) {
                    $result->addRateByAttributes($channelCode, $localeCode, $data['attributes'] ?? []);
                }

                $status = $data['status'] ?? CriterionEvaluationResultStatus::NOT_APPLICABLE;

                $result->addStatus($channelCode, $localeCode, new CriterionEvaluationResultStatus($status));
            }
        }

        return $result;
    }

    public static function aWritableCompletenessCalculationResult(array $resultData = ['a_channel' => ['en_US' => ['rate' => 100, 'attributes' => []]]]): Write\CompletenessCalculationResult
    {
        $result = new Write\CompletenessCalculationResult();

        foreach ($resultData as $channel => $locales) {
            foreach ($locales as $locale => $data) {
                $channelCode = new ChannelCode($channel);
                $localeCode = new LocaleCode($locale);

                if (array_key_exists('rate', $data) && is_numeric($data['rate'])) {
                    $result->addRate($channelCode, $localeCode, new Rate($data['rate']));
                }

                if (array_key_exists('attributes', $data)) {
                    $result->addMissingAttributes($channelCode, $localeCode, $data['attributes'] ?? []);
                }
            }
        }

        return $result;
    }
}
