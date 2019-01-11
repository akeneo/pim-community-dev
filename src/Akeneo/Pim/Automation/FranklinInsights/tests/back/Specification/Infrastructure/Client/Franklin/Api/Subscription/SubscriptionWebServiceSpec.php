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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\RequestCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\GuzzleClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\UriGenerator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * TODO: There are lot of spec to add.
 */
class SubscriptionWebServiceSpec extends ObjectBehavior
{
    public function let(GuzzleClient $httpClient): void
    {
        $uriGenerator = new UriGenerator('BASE_URI');
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_a_subscription_web_service(): void
    {
        $this->shouldBeAnInstanceOf(SubscriptionWebService::class);
    }

    public function it_is_an_authenticated_webservice(): void
    {
        $this->shouldImplement(AuthenticatedApiInterface::class);
    }

    public function it_is_a_subscription_api(): void
    {
        $this->shouldImplement(SubscriptionWebService::class);
    }

    public function it_throws_an_insufficient_credit_exception_on_subscription(
        $httpClient
    ): void {
        $request = new Request('POST', 'BASE_URI/my/uri');
        $response = new Response(402);
        $clientException = new ClientException('An exception message', $request, $response);
        $httpClient->request('POST', 'BASE_URI/api/subscriptions', ['form_params' => []])->willThrow($clientException);

        $this
            ->shouldThrow(new InsufficientCreditsException())
            ->during('subscribe', [new RequestCollection()]);
    }

    public function it_calls_a_delete_request_for_unsubscription($httpClient): void
    {
        $httpClient->request('DELETE', 'BASE_URI/api/subscriptions/foo-bar')->shouldBeCalled();

        $this->unsubscribeProduct('foo-bar')->shouldReturn(null);
    }

    public function it_throws_franklin_server_exception_when_server_exception_occurs_during_unsubscription(
        $httpClient
    ): void {
        $httpClient->request('DELETE', 'BASE_URI/api/subscriptions/foo-bar')->willThrow(ServerException::class);

        $this->shouldThrow(FranklinServerException::class)->during('unsubscribeProduct', ['foo-bar']);
    }

    public function it_throws_bad_request_exception_when_client_exception_occurs_during_unsubscription(
        $httpClient
    ): void {
        $httpClient->request('DELETE', 'BASE_URI/api/subscriptions/foo-bar')->willThrow(ClientException::class);

        $this->shouldThrow(BadRequestException::class)->during('unsubscribeProduct', ['foo-bar']);
    }

    // Next specs are about fetchProducts() and nothing is needed more about this method.
    public function it_fetches_product_subscriptions(
        $httpClient,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $httpClient
            ->request('GET', 'BASE_URI/api/subscriptions/updated-since/yesterday')
            ->willReturn($response);

        $jsonData = $this->loadFakeFetchJsonData();
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($jsonData);

        $subscriptionsPage = $this->fetchProducts();
        $subscriptionsPage->shouldReturnAnInstanceOf(SubscriptionsCollection::class);
    }

    public function it_fetches_product_subscriptions_from_an_uri(
        $httpClient,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $httpClient->request('GET', 'BASE_URI/my/uri')->willReturn($response)->shouldBeCalled();

        $jsonData = $this->loadFakeFetchJsonData();
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($jsonData);

        $this->fetchProducts('/my/uri')->shouldReturnAnInstanceOf(SubscriptionsCollection::class);
    }

    public function it_throws_a_franklin_server_exception_if_something_went_wrong_with_franklin_during_fetching(
        $httpClient
    ): void {
        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow(ServerException::class);

        $this->shouldThrow(FranklinServerException::class)->during('fetchProducts', ['/my/uri']);
    }

    public function it_throws_an_invalid_token_exception_during_fetching($httpClient): void
    {
        $request = new Request('GET', '/my/uri');
        $response = new Response(401);
        $clientException = new ClientException('An exception message', $request, $response);

        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow($clientException);

        $this->shouldThrow(InvalidTokenException::class)->during('fetchProducts', ['/my/uri']);
    }

    public function it_throws_a_bad_request_exception_during_fetching($httpClient): void
    {
        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow(ClientException::class);

        $this->shouldThrow(BadRequestException::class)->during('fetchProducts', ['/my/uri']);
    }

    /**
     * @return string
     */
    private function loadFakeFetchJsonData()
    {
        return <<<JSON
            {
              "_links": {
                "subscription": []
              },
              "_embedded": {
                "subscription": []
              },
              "total": 0,
              "limit": 100
            }
JSON;
    }
}
