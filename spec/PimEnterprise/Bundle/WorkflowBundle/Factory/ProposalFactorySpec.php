<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

class PropositionFactorySpec extends ObjectBehavior
{
    function it_should_creates_a_proposal(
        AbstractProduct $product
    ) {
        $product->getLocale()->willReturn('foo');

        $proposal = $this->createProposition($product, 'bar', []);

        $proposal->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition');
        $proposal->getProduct()->shouldReturn($product);
        $proposal->getLocale()->shouldReturn('foo');
        $proposal->getAuthor()->shouldReturn('bar');
        $proposal->getChanges()->shouldReturn([]);
    }
}
