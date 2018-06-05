<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AttributeGroupViewRightFilterSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker);
    }

    public function it_does_not_filter_an_attribute_group_if_the_user_is_granted_to_view_attribute_group($authorizationChecker, AttributeGroupInterface $marketing)
    {
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(true);

        $this->filterObject($marketing, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_an_attribute_group_if_the_user_is_not_granted_to_view_attribute_group($authorizationChecker, AttributeGroupInterface $marketing)
    {
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(false);

        $this->filterObject($marketing, 'pim:product_value:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:attribute:view', ['locales' => ['en_US']]]);
    }
}
