<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\Axis;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class ComputeEnrichmentRatesSpec extends ObjectBehavior
{
    public function it_computes_enrichment_rates_for_a_product()
    {
        $channelCode = new ChannelCode('ecommerce');
        $localeUs = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $rates = (new ChannelLocaleRateCollection())
            ->addRate($channelCode, $localeUs, new Rate(100))
            ->addRate($channelCode, $localeFr, new Rate(90));

        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
            ->add(new Read\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('completeness_of_required_attributes'),
                new ProductId(42),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::done(),
                new Read\CriterionEvaluationResult($rates, new CriterionEvaluationResultStatusCollection(), []),
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
        ));

        $expectedAxisRateCollection = new AxisRateCollection();
        $expectedAxisRateCollection->addCriterionRateCollection($rates);
        $this->compute($criteriaEvaluations)->shouldBeLike($expectedAxisRateCollection);
    }

    public function it_does_not_return_any_rates_if_there_is_no_completeness_evaluation()
    {
        $criteriaEvaluations = (new Read\CriterionEvaluationCollection());

        $this->compute($criteriaEvaluations)->shouldBeLike(new AxisRateCollection());
    }

    public function it_does_not_return_any_rates_if_the_latest_completeness_evaluation_has_no_result()
    {
        $criteriaEvaluations = (new Read\CriterionEvaluationCollection())
            ->add(new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('completeness_of_required_attributes'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::error(),
            null,
            null,
            null
        ));

        $this->compute($criteriaEvaluations)->shouldBeLike(new AxisRateCollection());
    }
}
