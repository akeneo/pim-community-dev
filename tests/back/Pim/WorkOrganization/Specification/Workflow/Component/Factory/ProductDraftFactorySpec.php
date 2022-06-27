<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\EntityWithValuesDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

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
        $draftSource = new DraftSource('pim','PIM', 'admin', 'Administrator');

        $uuid = Uuid::uuid4();
        $product->getUuid()->willReturn($uuid);
        $productRepository->find($uuid)->willReturn($fullProduct);

        $productDraft = $this->createEntityWithValueDraft($product, $draftSource);

        $productDraft->shouldBeAnInstanceOf(ProductDraft::class);
        $productDraft->getEntityWithValue()->shouldReturn($fullProduct);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getAuthorLabel()->shouldReturn('Administrator');
        $productDraft->getSource()->shouldReturn('pim');
        $productDraft->getSourceLabel()->shouldReturn('PIM');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
