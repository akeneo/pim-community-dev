<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

class PropositionFactorySpec extends ObjectBehavior
{
    function it_should_creates_a_proposition(
        AbstractProduct $product
    ) {
        $proposition = $this->createProposition($product, 'admin', 'en_US');

        $proposition->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition');
        $proposition->getProduct()->shouldReturn($product);
        $proposition->getLocale()->shouldReturn('en_US');
        $proposition->getAuthor()->shouldReturn('admin');
        $proposition->getChanges()->shouldReturn([]);
    }

    function it_fallbacks_on_product_locale(
        AbstractProduct $product
    ) {
        $product->getLocale()->willReturn('fr_FR');

        $proposition = $this->createProposition($product, 'admin');

        $proposition->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition');
        $proposition->getProduct()->shouldReturn($product);
        $proposition->getLocale()->shouldReturn('fr_FR');
        $proposition->getAuthor()->shouldReturn('admin');
        $proposition->getChanges()->shouldReturn([]);
    }
}
