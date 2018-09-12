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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionWebservice;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\PimAiServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class SubscriptionWebserviceSpec extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_a_subscription_web_service()
    {
        $this->shouldBeAnInstanceOf(SubscriptionWebservice::class);
    }

    public function it_is_a_subscription_api()
    {
        $this->shouldImplement(SubscriptionApiInterface::class);
    }

    public function it_calls_a_delete_request_on_subscription_id($uriGenerator, $httpClient)
    {
        $subscriptionId = 'foo-bar';

        $uriGenerator
            ->generate('/subscriptions/'. $subscriptionId)
            ->willReturn('unsubscription-route');

        $httpClient->request('DELETE', 'unsubscription-route')->shouldBeCalled();

        $this->unsubscribeProduct($subscriptionId)->shouldReturn(null);
    }

    public function it_throws_pim_ai_server_exception_on_server_exception($uriGenerator, $httpClient)
    {
        $subscriptionId = 'foo-bar';

        $uriGenerator
            ->generate('/subscriptions/'. $subscriptionId)
            ->willReturn('unsubscription-route');

        $httpClient
            ->request('DELETE', 'unsubscription-route')
            ->willThrow(ServerException::class);

        $this
            ->shouldThrow(
                new PimAiServerException('Something went wrong on PIM.ai side during product subscription: ')
            )
            ->during('unsubscribeProduct', [$subscriptionId]);
    }

    public function it_throws_bad_request_exception_on_client_exception($uriGenerator, $httpClient)
    {
        $subscriptionId = 'foo-bar';

        $uriGenerator
            ->generate('/subscriptions/'. $subscriptionId)
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
}
