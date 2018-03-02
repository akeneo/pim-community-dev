<?php

namespace spec\Pim\Bundle\UserBundle\Security;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Persistence\ORM\Query\FindAuthenticatedUser;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class UserProviderSpec extends ObjectBehavior
{
    function let(FindAuthenticatedUser $findAuthenticatedUserQuery)
    {
        $this->beConstructedWith($findAuthenticatedUserQuery);
    }

    function it_loads_a_user_by_its_username($findAuthenticatedUserQuery, AdvancedUserInterface $julia)
    {
        $findAuthenticatedUserQuery->__invoke('julia')->willReturn($julia);
        $this->loadUserByUsername('julia')->shouldReturn($julia);
    }

    function it_refreshes_a_user($findAuthenticatedUserQuery, AdvancedUserInterface $julia)
    {
        $findAuthenticatedUserQuery->__invoke('julia')->willReturn($julia);
        $julia->getUsername()->willReturn('julia');
        $this->refreshUser($julia)->shouldReturn($julia);
    }

    function it_throws_an_exception_if_user_does_not_exist($findAuthenticatedUserQuery)
    {
        $findAuthenticatedUserQuery->__invoke('jean-pacôme')->willThrow(ResourceNotFoundException::class);
        $this->shouldThrow(UsernameNotFoundException::class)
             ->during('loadUserByUsername', ['jean-pacôme']);
    }
}
