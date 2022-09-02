<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2Client;
use PhpSpec\ObjectBehavior;

class AuthorizationCodeGeneratorSpec extends ObjectBehavior
{
    public function let(
        ClientManagerInterface $clientManager,
        UserRepositoryInterface $userRepository,
        IOAuth2GrantCode $storage,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        ClockInterface $clock
    ): void {
        $this->beConstructedWith(
            $clientManager,
            $userRepository,
            $storage,
            $randomCodeGenerator,
            $clock
        );
    }

    public function it_generates_an_authorization_code(
        ClientManagerInterface $clientManager,
        UserRepositoryInterface $userRepository,
        IOAuth2GrantCode $storage,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        ClockInterface $clock,
        \DateTimeImmutable $now,
        IOAuth2Client $client,
        UserInterface $pimUser
    ): void {
        $code = 'MjE3NTE3YjQ0MzcwYTU1YjZlZjRhMzZiZGQwOWZmMDhlMmFkMzIxNmM5YjhiYjg2M2QwMjg4ZGIzZjE5ZjAzMg';
        $appId = '2ef7885a-4951-4d5a-bd28-1a8988b9476e';
        $appUserId = 3;
        $userGroup = 'my_user_group';
        $fosClientId = 2;
        $appConfirmation = AppConfirmation::create(
            $appId,
            $appUserId,
            $userGroup,
            $fosClientId,
        );
        $redirectUri = 'https://foo.example.com/oauth/callback';
        $timestamp = 1634572000;
        $pimUserId = 1;

        $randomCodeGenerator->generate()->willReturn($code);
        $userRepository->find($pimUserId)->willReturn($pimUser);
        $clientManager->findClientBy(['id' => $fosClientId])->willReturn($client);
        $clock->now()->willReturn($now);
        $now->getTimestamp()->willReturn($timestamp);

        $storage->createAuthCode(
            $code,
            $client,
            $pimUser,
            $redirectUri,
            $timestamp + 30
        )
            ->shouldBeCalled();

        $this->generate($appConfirmation, $pimUserId, $redirectUri)->shouldReturn($code);
    }
}
