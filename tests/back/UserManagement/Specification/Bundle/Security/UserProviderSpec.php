<?php

namespace Specification\Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;

class UserProviderSpec extends ObjectBehavior
{
    function let(UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($userRepository);
    }

    function it_loads_a_user_by_its_username(UserRepositoryInterface $userRepository, UserInterface $julia)
    {
        $userRepository->findOneByIdentifier('julia')->willReturn($julia);
        $julia->isApiUser()->willReturn(false);
        $this->loadUserByUsername('julia')->shouldReturn($julia);
    }

    function it_throws_an_exception_if_username_does_not_exist(UserRepositoryInterface $userRepository)
    {
        $userRepository->findOneByIdentifier('jean-pacôme')->willReturn(null);
        $this->shouldThrow(UsernameNotFoundException::class)
             ->during('loadUserByUsername', ['jean-pacôme']);
    }

    function it_refreshes_a_user($userRepository, UserInterface $julia)
    {
        $userRepository->find(42)->willReturn($julia);
        $julia->getId()->willReturn(42);
        $julia->isApiUser()->shouldBeCalled();
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    function it_throw_an_exception_if_user_class_is_not_supported()
    {
        $julia = new User('julia', 'jambon');
        $this->shouldThrow(UnsupportedUserException::class)->during('refreshUser', [$julia]);
    }

    function it_throws_an_exception_if_user_cannot_be_refreshed(
        UserRepositoryInterface $userRepository,
        UserInterface $julia
    ) {
        $julia->getId()->willReturn(42);
        $userRepository->find(42)->willReturn(null);

        $this->shouldThrow(UsernameNotFoundException::class)
            ->during('refreshUser', [$julia]);
    }
}
