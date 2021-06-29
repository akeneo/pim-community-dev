<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductScoresSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        CriteriaEvaluationRegistry $criteriaEvaluationRegistry
    ) {
        $this->beConstructedWith($getLocalesByChannelQuery, $criteriaEvaluationRegistry);
    }

    public function it_computes_the_product_scores_from_the_product_evaluation(
        $getLocalesByChannelQuery,
        $criteriaEvaluationRegistry
    ) {
        $getLocalesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'mobile' => ['en_US', 'fr_FR'],
            'print' => ['en_US', 'fr_FR'],
        ]));

        $channelMobile = new ChannelCode('mobile');
        $channelPrint = new ChannelCode('print');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $criterionResultA = (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(100))
                ->addRate($channelMobile, $localeFr, new Rate(90))
                ->addRate($channelPrint, $localeEn, new Rate(60));
        $criterionResultB = (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(90))
                ->addRate($channelMobile, $localeFr, new Rate(80))
                ->addRate($channelPrint, $localeEn, new Rate(100));
        $criterionResultC = (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(81))
                ->addRate($channelPrint, $localeEn, new Rate(70));

        $criterionA = new CriterionCode('criterion_A');
        $criterionB = new CriterionCode('criterion_B');
        $criterionC = new CriterionCode('criterion_C');

        $criteriaEvaluations = (new CriterionEvaluationCollection())
            ->add(new CriterionEvaluation(
                $criterionA,
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new CriterionEvaluationResult($criterionResultA, new CriterionEvaluationResultStatusCollection(), [])
            ))
            ->add(new CriterionEvaluation(
                $criterionB,
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new CriterionEvaluationResult($criterionResultB, new CriterionEvaluationResultStatusCollection(), [])
            ))
            ->add(new CriterionEvaluation(
                $criterionC,
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new CriterionEvaluationResult($criterionResultC, new CriterionEvaluationResultStatusCollection(), [])
            ))
        ;

        $criteriaEvaluationRegistry->getCriterionCodes()->willReturn([$criterionA, $criterionB, $criterionC]);
        $criteriaEvaluationRegistry->getCriterionCoefficient($criterionA)->willReturn(2);
        $criteriaEvaluationRegistry->getCriterionCoefficient($criterionB)->willReturn(1);
        $criteriaEvaluationRegistry->getCriterionCoefficient($criterionC)->willReturn(1);

        $this->fromCriteriaEvaluations($criteriaEvaluations)->shouldBeLike(
            (new ChannelLocaleRateCollection)
                ->addRate($channelMobile, $localeEn, new Rate(93))
                ->addRate($channelMobile, $localeFr, new Rate(87))
                ->addRate($channelPrint, $localeEn, new Rate(72))
        );
    }
}
