<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetDashboardScoresQueryInterface;
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
    public function let(GetDashboardScoresQueryInterface $getDashboardScoresQuery)
    {
        $this->beConstructedWith($getDashboardScoresQuery);
    }

    public function it_returns_a_http_bad_request_response_if_an_invalid_category_code_is_given(
        GetDashboardScoresQueryInterface $getDashboardScoresQuery
    ) {
        $getDashboardScoresQuery->byCategory(Argument::cetera())->shouldNotBeCalled();

        $request = new Request(['category' => '']);

        $this->__invoke($request, 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse(['error' => 'A category code cannot be empty'], Response::HTTP_BAD_REQUEST));
    }

    public function it_returns_a_http_bad_request_response_if_an_invalid_family_code_is_given(
        GetDashboardScoresQueryInterface $getDashboardScoresQuery
    ) {
        $getDashboardScoresQuery->byFamily(Argument::cetera())->shouldNotBeCalled();

        $request = new Request(['family' => '']);

        $this->__invoke($request, 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse(['error' => 'A family code cannot be empty'], Response::HTTP_BAD_REQUEST));
    }

    public function it_returns_an_empty_response_if_there_is_no_rates(
        GetDashboardScoresQueryInterface $getDashboardRatesQuery
    ) {
        $getDashboardRatesQuery
            ->byCatalog(new ChannelCode('ecommerce'), new LocaleCode('en_US'), TimePeriod::daily())
            ->willReturn(null);

        $this->__invoke(new Request(), 'ecommerce', 'en_US', TimePeriod::DAILY)
            ->shouldBeLike(new JsonResponse([]));
    }
}
