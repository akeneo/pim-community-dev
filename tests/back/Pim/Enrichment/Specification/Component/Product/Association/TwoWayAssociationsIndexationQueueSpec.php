<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Association\TwoWayAssociationsIndexationQueue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class TwoWayAssociationsIndexationQueueSpec extends ObjectBehavior
{
    public function it_is_a_two_way_associations_indexation_queue(): void
    {
        $this->shouldHaveType(TwoWayAssociationsIndexationQueue::class);
    }

    public function it_queues_and_flushes_product_uuids(ProductInterface $product1, ProductInterface $product2): void
    {
        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $product1->getUuid()->willReturn($uuid1);
        $product2->getUuid()->willReturn($uuid2);

        $this->queueProduct($product1);
        $this->queueProduct($product2);

        $this->flushProductUuids()->shouldReturn([$uuid1, $uuid2]);
    }

    public function it_empties_the_queue_after_flushing_product_uuids(ProductInterface $product): void
    {
        $product->getUuid()->willReturn(Uuid::uuid4());

        $this->queueProduct($product);
        $this->flushProductUuids();

        $this->flushProductUuids()->shouldReturn([]);
    }

    public function it_queues_and_flushes_product_model_codes(
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ): void {
        $productModel1->getCode()->willReturn('a_product_model');
        $productModel2->getCode()->willReturn('another_product_model');

        $this->queueProductModel($productModel1);
        $this->queueProductModel($productModel2);

        $this->flushProductModelCodes()->shouldReturn(['a_product_model', 'another_product_model']);
    }

    public function it_empties_the_queue_after_flushing_product_model_codes(ProductModelInterface $productModel): void
    {
        $productModel->getCode()->willReturn('a_product_model');

        $this->queueProductModel($productModel);
        $this->flushProductModelCodes();

        $this->flushProductModelCodes()->shouldReturn([]);
    }
}
