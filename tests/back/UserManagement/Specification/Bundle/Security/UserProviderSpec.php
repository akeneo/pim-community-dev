<?php

namespace Specification\Akeneo\UserManagement\Bundle\Security;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProviderSpec extends ObjectBehavior
{
    function let(UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($userRepository);
    }

    function it_loads_a_user_by_its_username($userRepository, UserInterface $julia)
    {
        $userRepository->findOneByIdentifier('julia')->willReturn($julia);
        $this->loadUserByUsername('julia')->shouldReturn($julia);
    }

    function it_refreshes_a_user($userRepository, UserInterface $julia)
    {
        $userRepository->find(42)->willReturn($julia);
        $julia->getId()->willReturn(42);
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    function it_throws_an_exception_if_user_does_not_exist($userRepository)
    {
        $userRepository->findOneByIdentifier('jean-pacôme')->willReturn(null);
        $this->shouldThrow(UsernameNotFoundException::class)
             ->during('loadUserByUsername', ['jean-pacôme']);
    }
}
