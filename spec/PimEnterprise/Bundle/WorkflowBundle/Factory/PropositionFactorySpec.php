<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

class PropositionFactorySpec extends ObjectBehavior
{
    function it_should_creates_a_proposition(
        AbstractProduct $product
    ) {
        $product->getLocale()->willReturn('foo');

        $proposition = $this->createProposition($product, 'bar', []);

        $proposition->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition');
        $proposition->getProduct()->shouldReturn($product);
        $proposition->getLocale()->shouldReturn('foo');
        $proposition->getAuthor()->shouldReturn('bar');
        $proposition->getChanges()->shouldReturn([]);
    }
}
