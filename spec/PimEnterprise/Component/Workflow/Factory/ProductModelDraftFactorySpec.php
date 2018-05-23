<?php

namespace spec\PimEnterprise\Component\Workflow\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PimEnterprise\Component\Workflow\Factory\EntityWithValuesDraftFactory;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;

class ProductModelDraftFactorySpec extends ObjectBehavior
{
    public function let(ProductModelRepositoryInterface $productModelRepository)
    {
        $this->beConstructedWith($productModelRepository);
    }

    function it_should_implement()
    {
        $this->shouldImplement(EntityWithValuesDraftFactory::class);
    }

    function it_creates_a_product_draft($productModelRepository, ProductModelInterface $productModel, ProductModelInterface $fullProductModel)
    {
        $productModelRepository->find(1)->willReturn($fullProductModel);
        $productModel->getId()->willReturn(1);

        $productDraft = $this->createEntityWithValueDraft($productModel, 'admin');

        $productDraft->shouldBeAnInstanceOf(ProductModelDraft::class);
        $productDraft->getEntityWithValue()->shouldReturn($fullProductModel);
        $productDraft->getAuthor()->shouldReturn('admin');
        $productDraft->getChanges()->shouldReturn([]);
    }
}
