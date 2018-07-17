<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Filter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductAndProductModelDeleteRightFilterSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenInterface $token
    )
    {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($tokenStorage, $authorizationChecker);
    }

    public function it_does_not_filter_a_product_if_the_user_owns_it($authorizationChecker, ProductInterface $product)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);

        $this->filterObject($product, 'pim.enrich.product.delete', [])->shouldReturn(false);
    }

    public function it_does_not_filter_a_product_model_if_the_user_owns_it(
        $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(true);

        $this->filterObject($productModel, 'pim.enrich.product.delete', [])->shouldReturn(false);
    }

    public function it_filters_a_product_if_the_user_does_not_own_it($authorizationChecker, ProductInterface $product)
    {
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);

        $this->filterObject($product, 'pim.enrich.product.delete', [])->shouldReturn(true);
    }

    public function it_filters_a_product_model_if_the_user_does_not_own_it(
        $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN, $productModel)->willReturn(false);

        $this->filterObject($productModel, 'pim.enrich.product.delete', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_or_a_product_model(\StdClass $anOtherObject)
    {
        $this
            ->shouldThrow('\LogicException')
            ->during('filterObject', [$anOtherObject, 'pim.enrich.product.delete']);
    }
}
