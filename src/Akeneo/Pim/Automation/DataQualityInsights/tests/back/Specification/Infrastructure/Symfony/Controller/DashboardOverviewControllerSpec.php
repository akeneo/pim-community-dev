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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetDashboardRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardOverviewControllerSpec extends ObjectBehavior
{
    public function let(FeatureFlag $featureFlag, GetDashboardRatesQueryInterface $getDashboardRatesQuery)
    {
        $this->beConstructedWith($featureFlag, $getDashboardRatesQuery);
    }

    public function it_returns_a_http_not_found_response_if_the_feature_is_not_enabled(FeatureFlag $featureFlag)
    {
        $featureFlag->isEnabled()->willReturn(false);

        $this->__invoke(new Request(), 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse(null, Response::HTTP_NOT_FOUND));
    }

    public function it_returns_a_http_bad_request_response_if_an_invalid_category_code_is_given(
        FeatureFlag $featureFlag,
        GetDashboardRatesQueryInterface $getDashboardRatesQuery
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $getDashboardRatesQuery->byCategory(Argument::cetera())->shouldNotBeCalled();

        $request = new Request(['category' => '']);

        $this->__invoke($request, 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse(['error' => 'A category code cannot be empty'], Response::HTTP_BAD_REQUEST));
    }

    public function it_returns_a_http_bad_request_response_if_an_invalid_family_code_is_given(
        FeatureFlag $featureFlag,
        GetDashboardRatesQueryInterface $getDashboardRatesQuery
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $getDashboardRatesQuery->byFamily(Argument::cetera())->shouldNotBeCalled();

        $request = new Request(['family' => '']);

        $this->__invoke($request, 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse(['error' => 'A family code cannot be empty'], Response::HTTP_BAD_REQUEST));
    }

    public function it_returns_an_empty_response_if_there_is_no_rates(
        FeatureFlag $featureFlag,
        GetDashboardRatesQueryInterface $getDashboardRatesQuery
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $getDashboardRatesQuery
            ->byCatalog(new ChannelCode('ecommerce'), new LocaleCode('en_US'), TimePeriod::daily())
            ->willReturn(null);

        $this->__invoke(new Request(), 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse([]));
    }
}
