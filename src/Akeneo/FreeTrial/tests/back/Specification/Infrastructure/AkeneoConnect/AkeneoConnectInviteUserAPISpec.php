<?php

declare(strict_types=1);

namespace Specification\Akeneo\FreeTrial\Infrastructure\AkeneoConnect;

use Akeneo\FreeTrial\Domain\Exception\InvitationAlreadySentException;
use Akeneo\FreeTrial\Domain\Exception\InvitationFailedException;
use Akeneo\FreeTrial\Infrastructure\AkeneoConnect\AkeneoConnectInviteUserAPI;
use Akeneo\FreeTrial\Infrastructure\AkeneoConnect\APIClient;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class AkeneoConnectInviteUserAPISpec extends ObjectBehavior
{
    public function let(APIClient $client, LoggerInterface $logger)
    {
        $this->beConstructedWith($client, $logger);
    }

    public function it_successfully_sends_an_invitation(APIClient $client, ResponseInterface $response)
    {
        $client->inviteUser('toto@ziggy.com')->willReturn($response);
        $response->getStatusCode()->willReturn(Response::HTTP_OK);

        $this->inviteUser('toto@ziggy.com');
    }

    public function it_throws_an_exception_if_invitation_has_already_been_sent(
        APIClient $client, ResponseInterface $response
    ) {
        $client->inviteUser('toto@ziggy.com')->willReturn($response);
        $response->getStatusCode()->willReturn(Response::HTTP_BAD_REQUEST);
        $response->toArray(false)->willReturn([
            'error' => [
                'code' => AkeneoConnectInviteUserAPI::INVITATION_ALREADY_SENT,
            ]
        ]);

        $this->shouldThrow(InvitationAlreadySentException::class)->during('inviteUser', ['toto@ziggy.com']);
    }

    public function it_throws_an_exception_when_something_not_expected_happens(
        APIClient $client, ResponseInterface $response
    ) {
        $client->inviteUser('toto@ziggy.com')->willReturn($response);
        $response->getStatusCode()->willReturn(Response::HTTP_BAD_REQUEST);
        $response->toArray(false)->willReturn([
            'error' => [
                'code' => 'random error',
            ]
        ]);

        $this->shouldThrow(InvitationFailedException::class)->during('inviteUser', ['toto@ziggy.com']);
    }
}
