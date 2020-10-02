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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class ComputeAxisRatesSpec extends ObjectBehavior
{
    public function let(GetLocalesByChannelQueryInterface $getLocalesByChannelQuery)
    {
        $this->beConstructedWith($getLocalesByChannelQuery);
    }

    public function it_computes_the_rates_of_an_axis(
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        Axis $axis
    ) {
        $axis->getCode()->willReturn(new AxisCode('enrichment'));
        $axis->getCriteriaCodes()->willReturn([
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE),
        ]);
        $axis->getCriterionCoefficient(new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE))->willReturn(2);
        $axis->getCriterionCoefficient(new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE))->willReturn(1);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'mobile' => ['en_US', 'fr_FR'],
            'print' => ['en_US', 'fr_FR'],
        ]));

        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $spellingResult = new CriterionEvaluationResult(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(100))
                ->addRate($channelMobile, $localeFr, new Rate(90))
                ->addRate($channelPrint, $localeEn, new Rate(100)),
            new CriterionEvaluationResultStatusCollection(),
            []
        );
        $completenessOfRequiredAttributesResult = new CriterionEvaluationResult(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(90))
                ->addRate($channelMobile, $localeFr, new Rate(80))
                ->addRate($channelPrint, $localeEn, new Rate(100)),
            new CriterionEvaluationResultStatusCollection(),
            []
        );
        $completenessOfNonRequiredAttributesResult = new CriterionEvaluationResult(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(81))
                ->addRate($channelMobile, $localeFr, new Rate(71))
                ->addRate($channelPrint, $localeEn, new Rate(70)),
            new CriterionEvaluationResultStatusCollection(),
            []
        );
        $expectedEnrichmentRates = (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(87))
                ->addRate($channelMobile, $localeFr, new Rate(77))
                ->addRate($channelPrint, $localeEn, new Rate(90));

        $criteriaEvaluations = (new CriterionEvaluationCollection())
            ->add(new CriterionEvaluation(
                new CriterionCode('spelling_criterion'),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                $spellingResult
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                $completenessOfRequiredAttributesResult
            ))
            ->add(new CriterionEvaluation(
                new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                $completenessOfNonRequiredAttributesResult
            ))
        ;

        $this->compute($axis, $criteriaEvaluations)->shouldBeLike($expectedEnrichmentRates);
    }
}
