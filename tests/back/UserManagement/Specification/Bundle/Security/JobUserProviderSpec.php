<?php

namespace Specification\Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class JobUserProviderSpec extends ObjectBehavior
{
    public function let(UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($userRepository);
    }

    public function it_loads_a_user_by_its_username(UserRepositoryInterface $userRepository, UserInterface $julia)
    {
        $julia->isJobUser()->willReturn(true);
        $julia->isEnabled()->willReturn(true);
        $userRepository->findOneByIdentifier('julia')->willReturn($julia);
        $this->loadUserByUsername('julia')->shouldReturn($julia);
    }

    public function it_throws_an_exception_if_username_does_not_exist(UserRepositoryInterface $userRepository)
    {
        $userRepository->findOneByIdentifier('jean-pacôme')->willReturn(null);
        $this->shouldThrow(UserNotFoundException::class)
            ->during('loadUserByIdentifier', ['jean-pacôme']);
    }

    public function it_throws_an_exception_if_user_is_disabled(UserRepositoryInterface $userRepository, UserInterface $disabledGuy)
    {
        $disabledGuy->isJobUser()->willReturn(true);
        $disabledGuy->isEnabled()->willReturn(false);
        $userRepository->findOneByIdentifier('disabled-guy')->willReturn($disabledGuy);
        $this->shouldThrow(DisabledException::class)
            ->during('loadUserByIdentifier', ['disabled-guy']);
    }

    public function it_throws_an_exception_if_user_is_not_job_user(UserRepositoryInterface $userRepository, UserInterface $apiUser)
    {
        $apiUser->isJobUser()->willReturn(false);
        $userRepository->findOneByIdentifier('job-user')->willReturn($apiUser);
        $this->shouldThrow(UserNotFoundException::class)
            ->during('loadUserByIdentifier', ['job-user']);
    }

    public function it_refreshes_a_user($userRepository, UserInterface $julia)
    {
        $userRepository->find(42)->willReturn($julia);
        $julia->getId()->willReturn(42);
        $julia->isJobUser()->willReturn(true);
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    public function it_throw_an_exception_if_user_class_is_not_supported()
    {
        $julia = new InMemoryUser('julia', 'jambon');
        $this->shouldThrow(UnsupportedUserException::class)->during('refreshUser', [$julia]);
    }

    public function it_throws_an_exception_if_user_cannot_be_refreshed(
        UserRepositoryInterface $userRepository,
        UserInterface $julia
    ) {
        $julia->getId()->willReturn(42);
        $userRepository->find(42)->willReturn(null);

        $this->shouldThrow(UserNotFoundException::class)
            ->during('refreshUser', [$julia]);
    }
}
