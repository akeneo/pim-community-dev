<?php

namespace Specification\Akeneo\Pim\Permission\Component\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\EntityWithFamilyVariant\GrantedRemoveParent;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GrantedRemoveParentSpec extends ObjectBehavior
{
    function let(RemoveParentInterface $baseRemoveParent, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($baseRemoveParent, $authorizationChecker);
    }

    function it_is_a_remove_parent()
    {
        $this->shouldImplement(RemoveParentInterface::class);
        $this->shouldHaveType(GrantedRemoveParent::class);
    }

    function it_throws_an_exception_if_the_user_does_not_own_the_product(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $this->shouldThrow(ResourceAccessDeniedException::class)->during('from', [$product]);
    }

    function it_removes_the_parent_from_a_variant_product_if_the_user_oowns_the_product(
        AuthorizationCheckerInterface $authorizationChecker,
        RemoveParentInterface $baseRemoveParent,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $baseRemoveParent->from($product)->shouldBeCalled();

        $this->from($product);
    }
}
