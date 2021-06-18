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
    public function let(ClientInterface $httpClient, RetrievePimFQDN $retrievePimFQDN)
    {
        $this->beConstructedWith($httpClient, $retrievePimFQDN, '', '', '', '');
    }

    public function it_invites_a_user(
        ClientInterface $httpClient,
        ResponseInterface $tokenResponse,
        ResponseInterface $inviteUserResponse,
        RetrievePimFQDN $retrievePimFQDN,
        StreamInterface $body
    )
    {
        $retrievePimFQDN->__invoke()->willReturn('token');
        $httpClient->request(
            'POST',
            '/auth/realms/connect/protocol/openid-connect/token',
            Argument::any()
        )->willReturn($tokenResponse)->shouldBeCalled();

        $tokenResponse->getBody()->willReturn($body);
        $body->getContents()->willReturn(json_encode([
            'access_token' => 'token'
        ]));

        $httpClient->request('POST', '/api/v1/console/trial/invite', Argument::withEntry('headers', [
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer token',
        ]))
            ->willReturn($inviteUserResponse);

        $this->inviteUser('toto@ziggy.com')->shouldReturn($inviteUserResponse);
    }
}
