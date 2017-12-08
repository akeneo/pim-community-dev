<?php

namespace spec\PimEnterprise\Component\Security\Authorization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CachedAuthorizationCheckerSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage): void
    {
        $this->beConstructedWith($authorizationChecker, $tokenStorage);
    }

    function it_is_an_authorization_checker(): void
    {
        $this->shouldImplement(
            'Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface'
        );
    }

    function it_caches_previous_results_for_object_resources(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $resourceToCheck = new Resource('gold');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::EDIT, $resourceToCheck)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $authorizationChecker->isGranted(Attributes::OWN, $resourceToCheck)
            ->willReturn(false)
            ->shouldBeCalledTimes(1);

        $this->isGranted(Attributes::EDIT, $resourceToCheck);
        $this->isGranted(Attributes::OWN, $resourceToCheck);
        $this->isGranted(Attributes::OWN, $resourceToCheck);
    }

    function it_caches_previous_results_for_non_object_resources(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::VIEW, 2)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
    }

    function it_caches_previous_results_even_if_user_has_no_id(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::VIEW, 2)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->isGranted(Attributes::VIEW, 2);
        $this->isGranted(Attributes::VIEW, 2);
    }

    function it_does_not_use_the_cache_when_the_resource_has_been_modified(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $resourceToCheck = new Resource('gold');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::OWN, $resourceToCheck)
            ->willReturn(false)
            ->shouldBeCalledTimes(2);

        $this->isGranted(Attributes::OWN, $resourceToCheck);

        $resourceToCheck->setName('stone');
        $this->isGranted(Attributes::OWN, $resourceToCheck);
    }
}

final class Resource
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
