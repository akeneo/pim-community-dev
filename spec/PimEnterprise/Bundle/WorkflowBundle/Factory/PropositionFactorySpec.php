<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

class PropositionFactorySpec extends ObjectBehavior
{
    function it_should_creates_a_proposition(
        AbstractProduct $product
    ) {
        $proposition = $this->createProposition($product, 'admin');

        $proposition->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition');
        $proposition->getProduct()->shouldReturn($product);
        $proposition->getAuthor()->shouldReturn('admin');
        $proposition->getChanges()->shouldReturn([]);
    }
}
