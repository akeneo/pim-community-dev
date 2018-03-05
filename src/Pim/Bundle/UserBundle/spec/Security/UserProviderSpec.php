<?php

namespace spec\Pim\Bundle\UserBundle\Security;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Persistence\ORM\Query\FindUserForSecurity;
use Pim\Component\User\User\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderSpec extends ObjectBehavior
{
    function let(FindUserForSecurity $findUserForSecurityQuery)
    {
        $this->beConstructedWith($findUserForSecurityQuery);
    }

    function it_loads_a_user_by_its_username($findUserForSecurityQuery, UserInterface $julia)
    {
        $findUserForSecurityQuery->__invoke('julia')->willReturn($julia);
        $this->loadUserByUsername('julia')->shouldReturn($julia);
    }

    function it_refreshes_a_user($findUserForSecurityQuery, UserInterface $julia)
    {
        $findUserForSecurityQuery->__invoke('julia')->willReturn($julia);
        $julia->getUsername()->willReturn('julia');
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    function it_throws_an_exception_if_user_does_not_exist($findUserForSecurityQuery)
    {
        $findUserForSecurityQuery->__invoke('jean-pacôme')->willThrow(UsernameNotFoundException::class);
        $this->shouldThrow('Symfony\Component\Security\Core\Exception\UsernameNotFoundException')
             ->during('loadUserByUsername', ['jean-pacôme']);
    }
}
