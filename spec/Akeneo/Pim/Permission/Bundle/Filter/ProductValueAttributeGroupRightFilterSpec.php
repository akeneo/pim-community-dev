<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductValueAttributeGroupRightFilterSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker);
    }

    public function it_does_not_filter_a_product_value_if_the_user_is_granted_to_see_its_attribute_group($authorizationChecker, ValueInterface $price, AttributeInterface $priceAttribute, AttributeGroupInterface $marketing)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->getGroup()->willReturn($marketing);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_the_user_is_not_granted_to_see_its_attribute_group($authorizationChecker, ValueInterface $price, AttributeInterface $priceAttribute, AttributeGroupInterface $marketing)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->getGroup()->willReturn($marketing);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]]);
    }
}
