<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Authentication;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Authentication\AuthenticationWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ClientInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\UriGenerator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AuthenticationWebServiceSpec extends ObjectBehavior
{
    public function let(
        UriGenerator $uriGenerator,
        ClientInterface $httpClient
    ): void {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_an_authentication_web_service(): void
    {
        $this->shouldHaveType(AuthenticationWebService::class);
    }

    public function it_can_set_a_token($httpClient): void
    {
        $this->setToken('a-token');

        $httpClient->setToken('a-token')->shouldHaveBeenCalled();
    }

    public function it_authenticates_on_franklin_insights($uriGenerator, $httpClient): void
    {
        $uriGenerator->generate('/api/stats')->willReturn('https://domain.name/api/stats');

        $httpClient->request('GET', 'https://domain.name/api/stats', [
            'headers' => ['Authorization' => 'a-token'],
        ])->willReturn(new Response(200));

        $this->authenticate('a-token')->shouldReturn(true);
    }

    public function it_does_not_authenticate_if_response_is_not_OK($uriGenerator, $httpClient): void
    {
        $uriGenerator->generate('/api/stats')->willReturn('https://domain.name/api/stats');

        $httpClient->request('GET', 'https://domain.name/api/stats', [])->willReturn(new Response(203));

        $this->authenticate(null)->shouldReturn(false);
    }

    public function it_does_not_authenticate_if_a_client_exception_was_thrown($uriGenerator, $httpClient): void
    {
        $uriGenerator->generate('/api/stats')->willReturn('https://domain.name/api/stats');

        $httpClient->request('GET', 'https://domain.name/api/stats', [])->willThrow(ClientException::class);

        $this->authenticate(null)->shouldReturn(false);
    }

    public function it_does_not_authenticate_if_a_server_exception_was_thrown($uriGenerator, $httpClient): void
    {
        $uriGenerator->generate('/api/stats')->willReturn('https://domain.name/api/stats');

        $httpClient->request('GET', 'https://domain.name/api/stats', [])->willThrow(ServerException::class);

        $this->authenticate(null)->shouldReturn(false);
    }
}
