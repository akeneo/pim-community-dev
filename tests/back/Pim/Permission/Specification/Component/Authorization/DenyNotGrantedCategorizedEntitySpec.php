<?php

namespace Specification\Akeneo\Pim\Permission\Component\Authorization;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Authorization\DenyNotGrantedCategorizedEntity;
use Akeneo\Pim\Permission\Component\Exception\ResourceViewAccessDeniedException;
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
                new ResourceViewAccessDeniedException(
                    $productModel->getWrappedObject(),
                    'Product model "product_model" does not exist or you do not have permission to access it.'
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
            ->shouldNotThrow(ResourceViewAccessDeniedException::class)
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
                new ResourceViewAccessDeniedException(
                    $product->getWrappedObject(),
                    'Product "product" does not exist or you do not have permission to access it.'
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
            ->shouldNotThrow(ResourceViewAccessDeniedException::class)
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
                new ResourceViewAccessDeniedException(
                    $categoryAware->getWrappedObject(),
                    'This entity does not exist or you do not have permission to access it.'
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
            ->shouldNotThrow(ResourceViewAccessDeniedException::class)
            ->during('denyIfNotGranted', [$categoryAware]);
        $this->denyIfNotGranted($categoryAware)->shouldReturn(null);
    }
}
