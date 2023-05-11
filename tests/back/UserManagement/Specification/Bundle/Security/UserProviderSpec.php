<?php

namespace Specification\Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\User;

class UserProviderSpec extends ObjectBehavior
{
    function let(UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($userRepository);
    }

    function it_loads_a_user_by_its_username(UserRepositoryInterface $userRepository, UserInterface $julia)
    {
        $julia->isUiUser()->willReturn(true);
        $julia->isEnabled()->willReturn(true);
        $userRepository->findOneByIdentifier('julia')->willReturn($julia);
        $this->loadUserByIdentifier('julia')->shouldReturn($julia);
    }

    function it_throws_an_exception_if_username_does_not_exist(UserRepositoryInterface $userRepository)
    {
        $userRepository->findOneByIdentifier('jean-pacôme')->willReturn(null);
        $this->shouldThrow(UsernameNotFoundException::class)
            ->during('loadUserByIdentifier', ['jean-pacôme']);
    }

    function it_throws_an_exception_if_user_is_disabled(UserRepositoryInterface $userRepository, UserInterface $disabledGuy)
    {
        $disabledGuy->isUiUser()->willReturn(true);
        $disabledGuy->isEnabled()->willReturn(false);
        $userRepository->findOneByIdentifier('disabled-guy')->willReturn($disabledGuy);
        $this->shouldThrow(UserNotFoundException::class)
            ->during('loadUserByIdentifier', ['disabled-guy']);
    }

    function it_throws_an_exception_if_user_is_job_user(UserRepositoryInterface $userRepository, UserInterface $jobUser)
    {
        $jobUser->isUiUser()->willReturn(false);
        $userRepository->findOneByIdentifier('job-user')->willReturn($jobUser);
        $this->shouldThrow(UserNotFoundException::class)
            ->during('loadUserByIdentifier', ['job-user']);
    }

    function it_refreshes_a_user($userRepository, UserInterface $julia)
    {
        $userRepository->find(42)->willReturn($julia);
        $julia->getId()->willReturn(42);
        $julia->isUiUser()->willReturn(true); //todo check if mandatory
        $julia->isUiUser()->shouldBeCalled(); //todo check if mandatory
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    function it_throw_an_exception_if_user_class_is_not_supported()
    {
        $julia = new InMemoryUser('julia', 'jambon');
        $this->shouldThrow(UnsupportedUserException::class)->during('refreshUser', [$julia]);
    }

    function it_throws_an_exception_if_user_cannot_be_refreshed(
        UserRepositoryInterface $userRepository,
        UserInterface $julia
    ) {
        $julia->getId()->willReturn(42);
        $userRepository->find(42)->willReturn(null);

        $this->shouldThrow(UserNotFoundException::class)
            ->during('refreshUser', [$julia]);
    }
}
