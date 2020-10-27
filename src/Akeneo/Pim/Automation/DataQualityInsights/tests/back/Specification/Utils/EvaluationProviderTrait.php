<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

trait EvaluationProviderTrait
{
    private function aWriteCriterionEvaluation(string $code = 'a_criterion', string $status = CriterionEvaluationStatus::DONE, int $productId = 1234, Write\CriterionEvaluationResult $result = null): Write\CriterionEvaluation
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

    private function aWriteCriterionEvaluationResult(array $resultData = ['a_channel' => ['en_US' => ['an_attribute' => 100]]]): Write\CriterionEvaluationResult
    {
        $result = new Write\CriterionEvaluationResult();
        foreach ($resultData as $channel => $locales) {
            foreach ($locales as $locale => $data) {
                $result->addRateByAttributes(new ChannelCode($channel), new LocaleCode($locale), $data);
            }
        }

        return $result;
    }
}
