<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

class EvaluateImageEnrichment implements EvaluateCriterionInterface
{
    public const CRITERION_CODE = 'enrichment_image';

    private CalculateProductCompletenessInterface $completenessCalculator;

    private CriterionCode $code;

    private GetLocalesByChannelQueryInterface $localesByChannelQuery;

    public function __construct(CalculateProductCompletenessInterface $completenessCalculator, GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->code = new CriterionCode(self::CRITERION_CODE);

        $this->completenessCalculator = $completenessCalculator;
        $this->localesByChannelQuery = $localesByChannelQuery;
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $completenessResult = $this->completenessCalculator->calculate($criterionEvaluation->getProductId());

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $completenessResult);
            }
        }

        return $evaluationResult;
    }

    public function getCode(): CriterionCode
    {
        return $this->code;
    }

    private function evaluateChannelLocaleRate(
        Write\CriterionEvaluationResult $evaluationResult,
        ChannelCode $channelCode,
        LocaleCode $localeCode,
        Write\CompletenessCalculationResult $completenessResult
    ): void {
        $rate = $completenessResult->getRates()->getByChannelAndLocale($channelCode, $localeCode);

        if (null === $rate) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $missingAttributes = $completenessResult->getMissingAttributes()->getByChannelAndLocale($channelCode, $localeCode);

        $attributesRates = [];

        if (null !== $missingAttributes) {
            foreach ($missingAttributes as $attributeCode) {
                $attributesRates[$attributeCode] = 0;
            }
        }

        // The score is 100 when there is at least one image uploaded, 0 otherwise
        if (!$rate->isPerfect() && $rate->toInt() > 0) {
            $rate = new Rate(100);
        }

        $evaluationResult
            ->addRate($channelCode, $localeCode, $rate)
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelCode, $localeCode, $attributesRates)
        ;
    }
}
