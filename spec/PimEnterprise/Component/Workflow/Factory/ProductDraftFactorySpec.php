<?php

namespace spec\PimEnterprise\Component\Workflow\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Factory\EntityWithValuesDraftFactory;
use PimEnterprise\Component\Workflow\Model\ProductDraft;

class ProductDraftFactorySpec extends ObjectBehavior
{
    public function let(ProductRepositoryInterface $productRepository)
    {
        $this->beConstructedWith($productRepository);
    }

    function it_should_implement()
    {
        $this->shouldImplement(EntityWithValuesDraftFactory::class);
    }

    function it_creates_a_product_draft($productRepository, ProductInterface $product, ProductInterface $fullProduct)
    {
        $productRepository->find(1)->willReturn($fullProduct);
        $product->getId()->willReturn(1);

        $productDraft = $this->createEntityWithValueDraft($product, 'admin');

        $productDraft->shouldBeAnInstanceOf(ProductDraft::class);
        $productDraft->getEntityWithValue()->shouldReturn($fullProduct);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
