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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionWebservice;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\GuzzleClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\UriGenerator;
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
class SubscriptionWebserviceSpec extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, GuzzleClient $httpClient): void
    {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_a_subscription_web_service(): void
    {
        $this->shouldBeAnInstanceOf(SubscriptionWebservice::class);
    }

    public function it_is_an_authenticated_webservice(): void
    {
        $this->shouldImplement(AuthenticatedApiInterface::class);
    }

    public function it_is_a_subscription_api(): void
    {
        $this->shouldImplement(SubscriptionApiInterface::class);
    }

    public function it_calls_a_delete_request_on_subscription_id($uriGenerator, $httpClient): void
    {
        $subscriptionId = 'foo-bar';

        $uriGenerator
            ->generate('/api/subscriptions/' . $subscriptionId)
            ->willReturn('unsubscription-route');

        $httpClient->request('DELETE', 'unsubscription-route')->shouldBeCalled();

        $this->unsubscribeProduct($subscriptionId)->shouldReturn(null);
    }

    public function it_throws_franklin_server_exception_on_server_exception($uriGenerator, $httpClient): void
    {
        $subscriptionId = 'foo-bar';

        $uriGenerator
            ->generate('/api/subscriptions/' . $subscriptionId)
            ->willReturn('unsubscription-route');

        $httpClient
            ->request('DELETE', 'unsubscription-route')
            ->willThrow(ServerException::class);

        $this
            ->shouldThrow(
                new FranklinServerException('Something went wrong on Franklin side during product subscription: ')
            )
            ->during('unsubscribeProduct', [$subscriptionId]);
    }

    public function it_throws_bad_request_exception_on_client_exception($uriGenerator, $httpClient): void
    {
        $subscriptionId = 'foo-bar';

        $uriGenerator
            ->generate('/api/subscriptions/' . $subscriptionId)
            ->willReturn('unsubscription-route');

        $httpClient
            ->request('DELETE', 'unsubscription-route')
            ->willThrow(ClientException::class);

        $this
            ->shouldThrow(
                new BadRequestException('Something went wrong during product subscription: ')
            )
            ->during('unsubscribeProduct', [$subscriptionId]);
    }

    // Next specs are about fetchProducts() and nothing is needed more about this method.
    public function it_fetches_product_subscriptions(
        $uriGenerator,
        $httpClient,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $uriGenerator->generate('/api/subscriptions/updated-since/yesterday')->willReturn('route')->shouldBeCalled();
        $httpClient->request('GET', 'route')->willReturn($response)->shouldBeCalled();

        $data = <<<JSON
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

        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($data);

        $subscriptionsPage = $this->fetchProducts();
        $subscriptionsPage->shouldReturnAnInstanceOf(SubscriptionsCollection::class);
    }

    public function it_fetches_product_subscriptions_from_an_uri(
        $uriGenerator,
        $httpClient,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $uriGenerator->getBaseUri()->willReturn('BASE_URI')->shouldBeCalled();
        $httpClient->request('GET', 'BASE_URI/my/uri')->willReturn($response)->shouldBeCalled();

        $data = <<<JSON
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

        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn($data);

        $subscriptionsPage = $this->fetchProducts('/my/uri');
        $subscriptionsPage->shouldReturnAnInstanceOf(SubscriptionsCollection::class);
    }

    public function it_throws_a_franklin_server_exception_if_something_went_wrong_with_franklin(
        $uriGenerator,
        $httpClient
    ): void {
        $request = new Request('GET', '/my/uri');
        $response = new Response(500);
        $clientException = new ServerException('An exception message', $request, $response);

        $uriGenerator->getBaseUri()->willReturn('BASE_URI');
        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow($clientException);

        $this
            ->shouldThrow(
                new FranklinServerException(
                    'Something went wrong on Franklin side during product subscription: An exception message.'
                )
            )
            ->during('fetchProducts', ['/my/uri']);
    }

    public function it_throws_an_insufficient_credit_exception($uriGenerator, $httpClient): void
    {
        $request = new Request('GET', '/my/uri');
        $response = new Response(402);
        $clientException = new ClientException('An exception message', $request, $response);

        $uriGenerator->getBaseUri()->willReturn('BASE_URI');
        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow($clientException);

        $this
            ->shouldThrow(
                new InsufficientCreditsException('Not enough credits on Franklin to subscribe.')
            )
            ->during('fetchProducts', ['/my/uri']);
    }

    public function it_throws_an_invalid_token_exception($uriGenerator, $httpClient): void
    {
        $request = new Request('GET', '/my/uri');
        $response = new Response(403);
        $clientException = new ClientException('An exception message', $request, $response);

        $uriGenerator->getBaseUri()->willReturn('BASE_URI');
        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow($clientException);

        $this
            ->shouldThrow(
                new InvalidTokenException('The Franklin token is missing or invalid.')
            )
            ->during('fetchProducts', ['/my/uri']);
    }

    public function it_throws_a_bad_request_exception($uriGenerator, $httpClient): void
    {
        $request = new Request('GET', '/my/uri');
        $response = new Response(400);
        $clientException = new ClientException('You did something wrong', $request, $response);

        $uriGenerator->getBaseUri()->willReturn('BASE_URI');
        $httpClient->request('GET', 'BASE_URI/my/uri')->willThrow($clientException);

        $this
            ->shouldThrow(
                new BadRequestException('Something went wrong during product subscription: You did something wrong.')
            )
            ->during('fetchProducts', ['/my/uri']);
    }
}
