<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Security\Attributes;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LocaleViewRightFilterSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker);
    }

    public function it_does_not_filter_a_locale_if_the_user_is_granted_to_see_this_locale($authorizationChecker, LocaleInterface $enUS)
    {
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(true);

        $this->filterObject($enUS, 'pim:locale:view', [])->shouldReturn(false);
    }

    public function it_filters_a_locale_if_the_user_is_not_granted_to_see_this_locale($authorizationChecker, LocaleInterface $enUS)
    {
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(false);

        $this->filterObject($enUS, 'pim:locale:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_locale(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:locale:view', ['channels' => ['en_US']]]);
    }
}
