<?php

namespace spec\Pim\Bundle\UserBundle\Security;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

class UserProviderSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\UserBundle\Security\UserProvider');
    }

    function it_does_not_load_user_by_username($repository)
    {
        $repository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(new UsernameNotFoundException('No user with name "unknown" was found.'))
            ->during('loadUserByUsername', ['unknown']);
    }

    function it_loads_user_by_username($repository, UserInterface $user)
    {
        $repository->findOneByIdentifier('admin')->willReturn($user);

        $this->loadUserByUsername('admin')->shouldReturn($user);
    }

    function it_does_not_refresh_unsupported_user(SecurityUserInterface $user)
    {
        $this->shouldThrow(new UnsupportedUserException('Account is not supported'))
            ->during('refreshUser', [$user]);
    }

    function it_does_not_refresh_when_user_is_unknown($repository, UserInterface $user)
    {
        $repository->findOneByIdentifier('unknown')->willReturn(null);
        $user->getUsername()->willReturn('unknown');
        $user->getId()->willReturn(42);

        $this->shouldThrow(new UsernameNotFoundException('User with ID "42" could not be reloaded'))
            ->during('refreshUser', [$user]);
    }

    function it_refreshes_user($repository, UserInterface $user, UserInterface $refreshed)
    {
        $repository->findOneByIdentifier('admin')->willReturn($refreshed);
        $user->getUsername()->willReturn('admin');

        $this->refreshUser($user)->shouldReturn($refreshed);
    }

    function it_supports_user()
    {
        $this->supportsClass('Pim\Bundle\UserBundle\Entity\User')->shouldReturn(true);
    }

    function it_does_not_supports_unsupported_classes()
    {
        $this->supportsClass('stdClass')->shouldReturn(false);
    }
}
