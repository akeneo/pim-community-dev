<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Provider\Form;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductModelFormProviderSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($authorizationChecker);
    }

    function it_is_a_form_provider()
    {
        $this->shouldBeAnInstanceOf(FormProviderInterface::class);
    }

    function it_supports_only_product_model(
        ProductModelInterface $productModel,
        \stdClass $randomObject
    ) {
        $this->supports($randomObject)->shouldReturn(false);
        $this->supports($productModel)->shouldReturn(true);
    }

    function it_gets_the_right_form_for_a_product_model_depending_on_permission(
        $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(true);
        $this->getForm($productModel)->shouldReturn('pim-product-model-edit-form');

        $authorizationChecker->isGranted(Attributes::EDIT, $productModel)->willReturn(false);
        $this->getForm($productModel)->shouldReturn('pimee-product-model-view-form');
    }
}
