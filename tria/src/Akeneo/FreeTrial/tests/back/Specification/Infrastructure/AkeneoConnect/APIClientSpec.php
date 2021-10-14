<?php

declare(strict_types=1);

namespace Specification\Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Infrastructure\AkeneoConnect\APIClient;
use Akeneo\FreeTrial\Infrastructure\RetrievePimFQDN;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Stream\StreamInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

final class APIClientSpec extends ObjectBehavior
{
    public function let(ClientInterface $connectApi, ClientInterface $portalApi, RetrievePimFQDN $retrievePimFQDN)
    {
        $this->beConstructedWith($connectApi, $portalApi, $retrievePimFQDN, '', '', '', '');
    }

    public function it_invites_a_user(
        ClientInterface $connectApi,
        ClientInterface $portalApi,
        ResponseInterface $tokenResponse,
        ResponseInterface $inviteUserResponse,
        RetrievePimFQDN $retrievePimFQDN,
        StreamInterface $body
    )
    {
        $retrievePimFQDN->__invoke()->willReturn('token');
        $connectApi->request(
            'POST',
            APIClient::URI_CONNECT,
            Argument::any()
        )->willReturn($tokenResponse)->shouldBeCalled();

        $tokenResponse->getBody()->willReturn($body);
        $body->getContents()->willReturn(json_encode([
            'access_token' => 'token'
        ]));

        $portalApi->request('POST', APIClient::URI_INVITE_USER, Argument::withEntry('headers', [
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer token',
        ]))
            ->willReturn($inviteUserResponse);

        $this->inviteUser('toto@ziggy.com')->shouldReturn($inviteUserResponse);
    }
}
