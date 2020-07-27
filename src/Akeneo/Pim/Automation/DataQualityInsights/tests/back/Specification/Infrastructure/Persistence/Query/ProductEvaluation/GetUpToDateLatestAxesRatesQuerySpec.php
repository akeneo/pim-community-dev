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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class GetUpToDateLatestAxesRatesQuerySpec extends ObjectBehavior
{
    public function let(
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $this->beConstructedWith($getLatestProductAxesRatesQuery, $hasUpToDateEvaluationQuery);
    }

    public function it_returns_the_latest_product_axes_rates_if_the_evaluation_of_the_product_is_up_to_date(
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(true);

        $axesRates = (new AxisRateCollection())
            ->add(new AxisCode(Enrichment::AXIS_CODE), (new ChannelLocaleRateCollection()))
            ->add(new AxisCode(Consistency::AXIS_CODE), (new ChannelLocaleRateCollection()))
        ;

        $getLatestProductAxesRatesQuery->byProductId($productId)->willReturn($axesRates);

        $this->byProductId($productId)->shouldReturn($axesRates);
    }

    public function it_returns_empty_axes_rates_if_the_evaluation_of_the_product_is_outdated(
        GetLatestAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery
    ) {
        $productId = new ProductId(42);

        $hasUpToDateEvaluationQuery->forProductId($productId)->willReturn(false);
        $getLatestProductAxesRatesQuery->byProductId($productId)->shouldNotBeCalled();

        $this->byProductId($productId)->shouldBeLike(new AxisRateCollection());
    }
}
