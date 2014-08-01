<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

class ProductDraftFactorySpec extends ObjectBehavior
{
    function it_should_creates_a_proposition(
        AbstractProduct $product
    ) {
        $productDraft = $this->createProposition($product, 'admin');

        $productDraft->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition');
        $productDraft->getProduct()->shouldReturn($product);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
