<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\EntityWithValuesDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;

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
