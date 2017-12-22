<?php

namespace spec\PimEnterprise\Component\Workflow\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

class ProductDraftFactorySpec extends ObjectBehavior
{
    public function let(ProductRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($productRepository);
    }

    function it_creates_a_product_draft($productRepository, ProductInterface $product, ProductInterface $fullProduct)
    {
        $productRepository->find(1)->willReturn($fullProduct);
        $product->getId()->willReturn(1);

        $productDraft = $this->createProductDraft($product, 'admin');

        $productDraft->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Model\ProductDraft');
        $productDraft->getProduct()->shouldReturn($fullProduct);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
