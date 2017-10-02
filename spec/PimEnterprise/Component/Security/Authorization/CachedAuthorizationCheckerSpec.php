<?php

namespace spec\PimEnterprise\Component\Security\Authorization;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ReferableInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CachedAuthorizationCheckerSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage)
    {
        $this->beConstructedWith($authorizationChecker, $tokenStorage);
    }

    function it_is_an_authorization_checker()
    {
        $this->shouldImplement(
            'Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface'
        );
    }

    function it_caches_previous_results_of_referable_objects(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        AttributeInterface $attribute
    ) {
        $blueJean = new ReferableObject('blue_jean');
        $anotherBlueJean = new ReferableObject('blue_jean');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $attribute->getReference()->willReturn('color');

        $authorizationChecker->isGranted(Attributes::OWN, $blueJean)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW, $attribute)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::OWN], $anotherBlueJean)->shouldNotBeCalled();

        $this->isGranted(Attributes::OWN, $blueJean)->shouldReturn(true);
        $this->isGranted(Attributes::VIEW, $attribute)->shouldReturn(false);
        $this->isGranted([Attributes::OWN], $anotherBlueJean)->shouldReturn(true);
    }

    function it_does_not_cache_not_referable_objects(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $twoObject = new NotReferableObject('2');
        $twoAgainObject = new NotReferableObject('2');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::VIEW, $twoObject)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $twoAgainObject)->shouldBeCalled()->willReturn(false);

        $this->isGranted(Attributes::VIEW, $twoObject)->shouldReturn(false);
        $this->isGranted(Attributes::VIEW, $twoAgainObject)->shouldReturn(false);
    }

    function it_caches_previous_results_of_non_objects(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(3);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::VIEW, 2)->shouldBeCalledTimes(1)->willReturn(true);

        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
    }

    function it_caches_previous_results_even_if_user_has_no_id(
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(null);
        $user->getUsername()->willReturn('arthur_dent');

        $authorizationChecker->isGranted(Attributes::VIEW, 2)->shouldBeCalledTimes(1)->willReturn(true);

        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
        $this->isGranted(Attributes::VIEW, 2)->shouldReturn(true);
    }
}

class ReferableObject implements ReferableInterface
{
    private $reference;

    public function __construct($reference)
    {
        $this->reference = $reference;
    }

    public function getReference()
    {
        return $this->reference;
    }
}

class NotReferableObject {
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }
}
