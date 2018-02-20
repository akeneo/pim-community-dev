<?php

namespace spec\PimEnterprise\Component\Security\Authorization;

use Akeneo\Component\Classification\CategoryAwareInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Authorization\DenyNotGrantedCategorizedEntity;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DenyNotGrantedCategorizedEntitySpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($authorizationChecker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DenyNotGrantedCategorizedEntity::class);
    }

    function it_denies_not_granted_product_models(
        $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $productModel)->willReturn(false);
        $productModel->getCode()->willReturn('product_model');

        $this
            ->shouldThrow(
                new ResourceAccessDeniedException(
                    $productModel->getWrappedObject(),
                    'You can neither view, nor update, nor delete the product model "product_model", as it is only ' .
                    'categorized in categories on which you do not have a view permission.'
                )
            )
            ->during('denyIfNotGranted', [$productModel]);
    }

    function it_does_nothing_for_granted_product_models(
        $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $productModel)->willReturn(true);

        $this
            ->shouldNotThrow('PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException')
            ->during('denyIfNotGranted', [$productModel]);
        $this->denyIfNotGranted($productModel)->shouldReturn(null);
    }

    function it_denies_not_granted_products(
        $authorizationChecker,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $product)->willReturn(false);
        $product->getIdentifier()->willReturn('product');

        $this
            ->shouldThrow(
                new ResourceAccessDeniedException(
                    $product->getWrappedObject(),
                    'You can neither view, nor update, nor delete the product "product", as it is only categorized ' .
                    'in categories on which you do not have a view permission.'
                )
            )
            ->during('denyIfNotGranted', [$product]);
    }

    function it_does_nothing_for_granted_products(
        $authorizationChecker,
        ProductInterface $product
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $product)->willReturn(true);

        $this
            ->shouldNotThrow('PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException')
            ->during('denyIfNotGranted', [$product]);
        $this->denyIfNotGranted($product)->shouldReturn(null);
    }

    function it_denies_not_granted_category_aware_entity(
        $authorizationChecker,
        CategoryAwareInterface $categoryAware
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $categoryAware)->willReturn(false);

        $this
            ->shouldThrow(
                new ResourceAccessDeniedException(
                    $categoryAware->getWrappedObject(),
                    'You can neither view, nor update, nor delete this entity, as it is only categorized ' .
                    'in categories on which you do not have a view permission.'
                )
            )
            ->during('denyIfNotGranted', [$categoryAware]);
    }

    function it_does_nothing_for_granted_category_aware_entity(
        $authorizationChecker,
        CategoryAwareInterface $categoryAware
    ) {
        $authorizationChecker->isGranted(Attributes::VIEW, $categoryAware)->willReturn(true);

        $this
            ->shouldNotThrow('PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException')
            ->during('denyIfNotGranted', [$categoryAware]);
        $this->denyIfNotGranted($categoryAware)->shouldReturn(null);
    }
}
