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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
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
        $axis->getCode()->willReturn(new AxisCode('consistency'));
        $axis->getCriteriaCodes()->willReturn([
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            new CriterionCode(EvaluateUppercaseWords::CRITERION_CODE),
            new CriterionCode(EvaluateTitleFormatting::CRITERION_CODE),
        ]);
        $axis->getCriterionCoefficient(new CriterionCode(EvaluateSpelling::CRITERION_CODE))->willReturn(2);
        $axis->getCriterionCoefficient(new CriterionCode(EvaluateUppercaseWords::CRITERION_CODE))->willReturn(1);
        $axis->getCriterionCoefficient(new CriterionCode(EvaluateTitleFormatting::CRITERION_CODE))->willReturn(1);

        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'mobile' => ['en_US', 'fr_FR'],
            'print' => ['en_US', 'fr_FR'],
        ]));

        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $completenessResult = new CriterionEvaluationResult(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(100))
                ->addRate($channelMobile, $localeFr, new Rate(90))
                ->addRate($channelPrint, $localeEn, new Rate(100)),
            new CriterionEvaluationResultStatusCollection(),
            []
        );
        $spellingResult = new CriterionEvaluationResult(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(90))
                ->addRate($channelMobile, $localeFr, new Rate(80))
                ->addRate($channelPrint, $localeEn, new Rate(100)),
            new CriterionEvaluationResultStatusCollection(),
            []
        );
        $upperCaseResult = new CriterionEvaluationResult(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(81))
                ->addRate($channelMobile, $localeFr, new Rate(71))
                ->addRate($channelPrint, $localeEn, new Rate(70)),
            new CriterionEvaluationResultStatusCollection(),
            []
        );
        $expectedConsistencyRates = (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(87))
                ->addRate($channelMobile, $localeFr, new Rate(77))
                ->addRate($channelPrint, $localeEn, new Rate(90));

        $criteriaEvaluations = (new CriterionEvaluationCollection())
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                $completenessResult,
                null,
                null
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode(EvaluateSpelling::CRITERION_CODE),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                $spellingResult,
                null,
                null
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode(EvaluateUppercaseWords::CRITERION_CODE),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                $upperCaseResult,
                null,
                null
            ))
            ->add(new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode(EvaluateTitleFormatting::CRITERION_CODE),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending(),
                null,
                null,
                null
            ))
        ;

        $this->compute($axis, $criteriaEvaluations)->shouldBeLike($expectedConsistencyRates);
    }
}
