<?php

namespace spec\PimEnterprise\Component\Workflow\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class ProductDraftFactorySpec extends ObjectBehavior
{
    function it_creates_a_product_draft(
        ProductInterface $product
    ) {
        $productDraft = $this->createProductDraft($product, 'admin');

        $productDraft->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Model\ProductDraft');
        $productDraft->getProduct()->shouldReturn($product);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
