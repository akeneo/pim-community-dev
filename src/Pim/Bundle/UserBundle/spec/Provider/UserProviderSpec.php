<?php

namespace spec\Pim\Bundle\UserBundle\Provider;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $userRepository->findOneByIdentifier('julia')->willReturn($julia);
        $julia->getUsername()->willReturn('julia');
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    function it_throws_an_exception_if_user_does_not_exist($userRepository)
    {
        $userRepository->findOneByIdentifier('jean-pacôme')->willReturn(null);
        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UsernameNotFoundException')
             ->during('loadUserByUsername', ['jean-pacôme']);
    }
}
