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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics\StatisticsWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\GuzzleClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\UriGenerator;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\CreditsUsageStatistics;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class StatisticsWebServiceSpec extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, GuzzleClient $httpClient): void
    {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_a_statistics_webservice(): void
    {
        $this->shouldHaveType(StatisticsWebService::class);
    }

    public function it_is_an_authenticated_webservice(): void
    {
        $this->shouldImplement(AuthenticatedApiInterface::class);
    }

    public function it_fetches_credits_usage_statistics(
        ResponseInterface $response,
        StreamInterface $stream,
        $uriGenerator,
        $httpClient
    ): void {
        $uriGenerator->generate('/api/stats')->willReturn('my_route');

        $stream->getContents()->willReturn(
            json_encode(
                [
                    'stats' => [
                        'consumed' => 2,
                        'left' => 1,
                        'total' => 3,
                    ],
                ]
            )
        );
        $response->getBody()->willReturn($stream);

        $httpClient->request('GET', 'my_route')->willReturn($response);

        $this->getCreditsUsageStatistics()->shouldBeAnInstanceOf(CreditsUsageStatistics::class);
    }

    public function it_throws_an_exception_if_the_client_throws_a_server_exception(
        UriGenerator $uriGenerator,
        GuzzleClient $httpClient
    ): void {
        $uriGenerator->generate('/api/stats')->willReturn('my_route');
        $httpClient->request('GET', 'my_route')->willThrow(ServerException::class);

        $this->shouldThrow(FranklinServerException::class)->during('getCreditsUsageStatistics');
    }

    public function it_throws_an_exception_if_the_client_throws_a_client_exception(
        UriGenerator $uriGenerator,
        GuzzleClient $httpClient
    ): void {
        $uriGenerator->generate('/api/stats')->willReturn('my_route');
        $httpClient->request('GET', 'my_route')->willThrow(ClientException::class);

        $this->shouldThrow(BadRequestException::class)->during('getCreditsUsageStatistics');
    }
}
