<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class EvaluateCompletenessSpec extends ObjectBehavior
{
    public function let(GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->beConstructedWith($localesByChannelQuery);
    }

    public function it_evaluates_completeness_of_a_product(
        CalculateProductCompletenessInterface $completenessCalculator,
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'mobile' => ['en_US', 'fr_FR'],
            'print' => ['en_US', 'fr_FR'],
        ]));

        $productId = new ProductId(1);
        $criterionEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::pending()
        );

        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessCalculator->calculate($productId)->willReturn((new Write\CompletenessCalculationResult())
            ->addRate($channelMobile, $localeEn, new Rate(100))
            ->addRate($channelMobile, $localeFr, new Rate(85))
            ->addMissingAttributes($channelMobile, $localeFr, ['name', 'weight'])
            ->addRate($channelPrint, $localeEn, new Rate(92))
            ->addMissingAttributes($channelPrint, $localeEn, ['description'])
            ->addTotalNumberOfAttributes($channelMobile, $localeEn, 2)
            ->addTotalNumberOfAttributes($channelMobile, $localeFr, 6)
            ->addTotalNumberOfAttributes($channelPrint, $localeEn, 3)
        );

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelMobile, $localeEn, new Rate(100))
            ->addStatus($channelMobile, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeEn, [])
            ->addData('total_number_of_attributes', $channelMobile, $localeEn, 2)

            ->addRate($channelMobile, $localeFr, new Rate(85))
            ->addStatus($channelMobile, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeFr, ['name' => 0, 'weight' => 0])
            ->addData('total_number_of_attributes', $channelMobile, $localeFr, 6)

            ->addRate($channelPrint, $localeEn, new Rate(92))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeEn, ['description' => 0])
            ->addData('total_number_of_attributes', $channelPrint, $localeEn, 3)

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate($completenessCalculator, $criterionEvaluation)->shouldBeLike($expectedResult);
    }
}
