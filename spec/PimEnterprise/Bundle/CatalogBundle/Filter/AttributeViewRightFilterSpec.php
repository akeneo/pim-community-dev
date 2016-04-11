<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AttributeViewRightFilterSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker);
    }

    public function it_does_not_filter_an_attribute_if_the_user_is_granted_to_view_attribute_group($authorizationChecker, AttributeInterface $price, AttributeGroupInterface $marketing)
    {
        $price->getGroup()->willReturn($marketing);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_an_attribute_if_the_user_is_not_granted_to_view_attribute_group($authorizationChecker, AttributeInterface $price, AttributeGroupInterface $marketing)
    {
        $price->getGroup()->willReturn($marketing);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $price)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$price, 'pim:attribute:view', ['locales' => ['en_US']]]);
    }

    public function it_should_not_do_unnecessary_requests(
        $authorizationChecker,
        AttributeInterface $price,
        AttributeInterface $name,
        AttributeGroupInterface $marketing
    ) {
        $price->getGroup()->willReturn($marketing);
        $name->getGroup()->willReturn($marketing);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->shouldBeCalledTimes(1);

        $this->filterObject($price, 'pim:product_value:view', []);
    }
}
