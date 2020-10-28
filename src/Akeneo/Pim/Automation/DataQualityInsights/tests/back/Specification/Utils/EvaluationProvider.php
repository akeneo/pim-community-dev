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
                $rate = $data['rate'] ?? 100;
                $status = $data['status'] ?? CriterionEvaluationResultStatus::DONE;
                $attributes = $data['attributes'] ?? [];

                $result
                    ->addRate($channelCode, $localeCode, new Rate($rate))
                    ->addStatus($channelCode, $localeCode, new CriterionEvaluationResultStatus($status))
                    ->addRateByAttributes($channelCode, $localeCode, $attributes)
                ;
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

                $result->addRate($channelCode, $localeCode, new Rate($data['rate'] ?? 0));
                $result->addMissingAttributes($channelCode, $localeCode, $data['attributes'] ?? []);
            }
        }

        return $result;
    }
}
