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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\AxisRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\ProductEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Domain\Model\Axis\Consistency;
use PhpSpec\ObjectBehavior;

final class GetUpToDateProductEvaluationQuerySpec extends ObjectBehavior
{
    public function let(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        AxisRegistry $axisRegistry,
        Axis $consistencyAxis
    ) {
        $consistencyAxis->getCriteriaCodes()->willReturn([
            new CriterionCode('consistency_spelling'),
            new CriterionCode('consistency_textarea_lowercase_words'),
            new CriterionCode('consistency_textarea_uppercase_words'),
            new CriterionCode('consistency_attribute_spelling'),
            new CriterionCode('consistency_attribute_option_spelling'),
        ]);
        $consistencyAxis->getCode()->willReturn(new AxisCode(Consistency::AXIS_CODE));
        $axisRegistry->all()->willReturn([
            new Enrichment(),
            $consistencyAxis,
        ]);
        $this->beConstructedWith($getCriteriaEvaluationsByProductIdQuery, $getLatestProductAxesRatesQuery, $axisRegistry);
    }

    public function it_returns_the_latest_product_evaluation_for_a_product_id(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery
    ) {
        $productId = new ProductId(42);
        $enrichment = new Enrichment();
        $consistency = new Consistency();

        $spellingEvaluation = $this->givenASpellingEvaluation($productId);
        $completenessEvaluation = $this->givenACompletenessEvaluation($productId);
        $criteriaEvaluations = (new CriterionEvaluationCollection())
            ->add($spellingEvaluation)
            ->add($completenessEvaluation)
        ;
        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($criteriaEvaluations);

        $axesRates = $this->givenAxesRates();
        $getLatestProductAxesRatesQuery->byProductId($productId)->willReturn($axesRates);

        $expectedEnrichmentRates = $axesRates->get($enrichment->getCode());
        $expectedEnrichmentCriteriaEvaluations = (new CriterionEvaluationCollection())->add($completenessEvaluation);
        $expectedEnrichmentEvaluation = new AxisEvaluation($enrichment->getCode(), $expectedEnrichmentRates, $expectedEnrichmentCriteriaEvaluations);

        $expectedConsistencyRates = $axesRates->get($consistency->getCode());
        $expectedConsistencyCriteriaEvaluations = (new CriterionEvaluationCollection())->add($spellingEvaluation);
        $expectedConsistencyEvaluation = new AxisEvaluation($consistency->getCode(), $expectedConsistencyRates, $expectedConsistencyCriteriaEvaluations);

        $expectedAxesEvaluations = (new AxisEvaluationCollection())
            ->add($expectedEnrichmentEvaluation)
            ->add($expectedConsistencyEvaluation)
        ;
        $expectedProductEvaluation = new ProductEvaluation($productId, $expectedAxesEvaluations);

        $this->execute($productId)->shouldBeLike($expectedProductEvaluation);
    }

    public function it_returns_an_empty_product_evaluation_if_there_is_no_criterion_evaluation(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery
    ) {
        $productId = new ProductId(42);
        $enrichment = new Enrichment();
        $consistency = new Consistency();

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn(new CriterionEvaluationCollection());
        $getLatestProductAxesRatesQuery->byProductId($productId)->willReturn(new AxisRateCollection());

        $expectedAxesEvaluations = (new AxisEvaluationCollection())
            ->add(new AxisEvaluation($enrichment->getCode(), new ChannelLocaleRateCollection(), new CriterionEvaluationCollection))
            ->add(new AxisEvaluation($consistency->getCode(), new ChannelLocaleRateCollection(), new CriterionEvaluationCollection))
        ;
        $expectedProductEvaluation = new ProductEvaluation($productId, $expectedAxesEvaluations);

        $this->execute($productId)->shouldBeLike($expectedProductEvaluation);
    }

    private function givenASpellingEvaluation(ProductId $productId): CriterionEvaluation
    {
        $evaluateSpellingRates = (new ChannelLocaleRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(88))
            ->addRate(new ChannelCode('mobile'), new LocaleCode('fr_FR'), new Rate(76))
        ;
        $evaluateSpellingStatus = (new CriterionEvaluationResultStatusCollection())
            ->add(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->add(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->add(new ChannelCode('mobile'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::done())
        ;
        $evaluateSpellingData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["description"],
                ],
                "mobile" => [
                    "fr_FR" => ["description", "short_description"],
                ]
            ]
        ];

        return new CriterionEvaluation(
            new CriterionCode('consistency_spelling'),
            $productId,
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::done(),
            new CriterionEvaluationResult($evaluateSpellingRates, $evaluateSpellingStatus, $evaluateSpellingData)
        );
    }

    private function givenACompletenessEvaluation(ProductId $productId): CriterionEvaluation
    {
        $completenessOfRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(92));

        $completenessOfRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done());

        $completenessOfRequiredAttributesData = [
            "attributes" => [
                "ecommerce" => ['title']
            ]
        ];

        return new CriterionEvaluation(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
            $productId,
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::done(),
            new CriterionEvaluationResult($completenessOfRequiredAttributesRates, $completenessOfRequiredAttributesStatus, $completenessOfRequiredAttributesData)
        );
    }

    private function givenAxesRates(): AxisRateCollection
    {
        $channelEcommerce = new ChannelCode('ecommerce');
        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        return (new AxisRateCollection())
            ->add(new AxisCode(Enrichment::AXIS_CODE), (new ChannelLocaleRateCollection())
                ->addRate($channelEcommerce, $localeEn, new Rate(92))
            )
            ->add(new AxisCode(Consistency::AXIS_CODE), (new ChannelLocaleRateCollection())
                ->addRate($channelEcommerce, $localeEn, new Rate(87))
                ->addRate($channelMobile, $localeFr, new Rate(73))
            );
    }
}
