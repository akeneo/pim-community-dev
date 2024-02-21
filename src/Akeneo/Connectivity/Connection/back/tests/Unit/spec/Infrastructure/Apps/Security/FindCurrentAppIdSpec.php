<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Application\Apps\Security\FindCurrentAppIdInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\FindCurrentAppId;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FindCurrentAppIdSpec extends ObjectBehavior
{
    public function let(TokenStorageInterface $tokenStorage): void
    {
        $this->beConstructedWith($tokenStorage);
    }

    public function it_is_a_find_user_app_id(): void
    {
        $this->shouldHaveType(FindCurrentAppId::class);
        $this->shouldImplement(FindCurrentAppIdInterface::class);
    }

    public function it_finds_user_app_id(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $user->getProperty('app_id')->willReturn('an_app_id');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $this->execute()->shouldReturn('an_app_id');
    }

    public function it_returns_null_if_app_id_is_not_set(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $user->getProperty('app_id')->willReturn(null);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $this->execute()->shouldReturn(null);
    }
}
