<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ProductDraftFactorySpec extends ObjectBehavior
{
    function it_creates_a_product_draft(
        ProductInterface $product
    ) {
        $productDraft = $this->createProductDraft($product, 'admin');

        $productDraft->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft');
        $productDraft->getProduct()->shouldReturn($product);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
