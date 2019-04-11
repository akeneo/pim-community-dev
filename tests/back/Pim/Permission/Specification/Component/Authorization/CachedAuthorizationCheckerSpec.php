<?php

namespace Specification\Akeneo\Pim\Permission\Component\Authorization;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CachedAuthorizationCheckerSpec extends ObjectBehavior
{
    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer
    ): void
    {
        $this->beConstructedWith($authorizationChecker, $tokenStorage, $normalizer);
    }

    function it_is_an_authorization_checker(): void
    {
        $this->shouldImplement(AuthorizationCheckerInterface::class);
    }

    function it_caches_previous_results_for_object_resources(
        $authorizationChecker,
        $tokenStorage,
        $normalizer,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $resourceToCheck = new Resource('gold');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');
        $normalizer->normalize($resourceToCheck, 'authorization')->willThrow(NotNormalizableValueException::class);

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
        $normalizer,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');
        $normalizer->normalize(Argument::any(), 'authorization')->shouldNotBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW, 2)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
    }

    function it_caches_previous_results_even_if_user_has_no_id(
        $authorizationChecker,
        $tokenStorage,
        $normalizer,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn('arthur_dent');
        $normalizer->normalize(Argument::any(), 'authorization')->shouldNotBeCalled();

        $authorizationChecker->isGranted(Attributes::VIEW, 2)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->isGranted(Attributes::VIEW, 2);
        $this->isGranted(Attributes::VIEW, 2);
    }

    function it_does_not_use_the_cache_when_the_resource_has_been_modified(
        $authorizationChecker,
        $tokenStorage,
        $normalizer,
        TokenInterface $token,
        UserInterface $user
    ): void
    {
        $resourceToCheck = new Resource('gold');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $normalizer->normalize($resourceToCheck, 'authorization')->willReturn(['name' => 'gold']);

        $authorizationChecker->isGranted(Attributes::OWN, $resourceToCheck)->willReturn(false)->shouldBeCalledTimes(2);

        $this->isGranted(Attributes::OWN, $resourceToCheck);

        $resourceToCheck->setName('stone');
        $normalizer->normalize($resourceToCheck, 'authorization')->willReturn(['name' => 'stone']);
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
