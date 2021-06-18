<?php

declare(strict_types=1);

namespace Specification\Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Infrastructure\AkeneoConnect\APIClient;
use Akeneo\FreeTrial\Infrastructure\RetrievePimFQDN;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

final class APIClientSpec extends ObjectBehavior
{
    public function let(ClientInterface $httpClient, RetrievePimFQDN $retrievePimFQDN)
    {
        $this->beConstructedWith($httpClient, $retrievePimFQDN, '', '', '', '', '');
    }

    public function it_invites_a_user(
        ClientInterface $httpClient,
        ResponseInterface $response,
        RetrievePimFQDN $retrievePimFQDN
    ) {
        $retrievePimFQDN->__invoke()->willReturn('token');
        $httpClient->request(Argument::cetera())->willReturn($response);
        $this->inviteUser('toto@ziggy.com')->shouldReturn($response);
    }
}
