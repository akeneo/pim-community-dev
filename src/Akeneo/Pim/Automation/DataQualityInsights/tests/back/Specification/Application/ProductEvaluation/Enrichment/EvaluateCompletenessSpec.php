<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        );

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate($channelMobile, $localeEn, new Rate(100))
            ->addStatus($channelMobile, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeEn, [])

            ->addRate($channelMobile, $localeFr, new Rate(85))
            ->addStatus($channelMobile, $localeFr, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelMobile, $localeFr, ['name' => 0, 'weight' => 0])

            ->addRate($channelPrint, $localeEn, new Rate(92))
            ->addStatus($channelPrint, $localeEn, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelPrint, $localeEn, ['description' => 0])

            ->addStatus($channelPrint, $localeFr, CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate($completenessCalculator, $criterionEvaluation)->shouldBeLike($expectedResult);
    }
}
