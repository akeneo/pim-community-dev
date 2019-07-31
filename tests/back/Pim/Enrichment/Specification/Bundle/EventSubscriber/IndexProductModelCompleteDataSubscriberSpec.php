<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductModelCompleteDataSubscriber;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductModelCompleteDataSubscriberSpec extends ObjectBehavior
{
    function let(IndexerInterface $productModelIndexer)
    {
        $this->beConstructedWith($productModelIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductModelCompleteDataSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE => ['computeNumberOfCompleteVariantProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['computeNumberOfCompleteVariantProducts', 300]
        ]);
    }

    function it_only_works_with_variant_product(
        $productModelIndexer,
        GenericEvent $event,
        ProductModelInterface $productModel,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($productModel);
        $event->getArguments()->willReturn([]);
        $productModelIndexer->index(Argument::any())->shouldNotBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event);

        $event->getSubject()->willReturn($product);
        $event->getArguments()->willReturn([]);
        $product->isVariant()->willReturn(false);
        $product->getParent()->willReturn(null);
        $productModelIndexer->index(Argument::any())->shouldNotBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event);
    }

    function it_computes_number_of_complete_variant_product_with_one_level(
        $productModelIndexer,
        GenericEvent $event,
        ProductInterface $product,
        ProductModelInterface $rootProductModel
    ) {
        $event->getSubject()->willReturn($product);
        $event->getArguments()->willReturn([]);
        $product->isVariant()->willReturn(true);
        $product->getParent()->willReturn($rootProductModel);
        $rootProductModel->getParent()->willReturn(null);

        $productModelIndexer->index($rootProductModel)->shouldBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event)->shouldReturn(null);
    }

    function it_computes_number_of_complete_variant_product_with_two_level(
        $productModelIndexer,
        GenericEvent $event,
        ProductInterface $product,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $subProductModel
    ) {
        $event->getSubject()->willReturn($product);
        $event->getArguments()->willReturn([]);
        $product->isVariant()->willReturn(true);
        $product->getParent()->willReturn($subProductModel);
        $subProductModel->getParent()->willReturn(null);
        $product->getParent()->willReturn($rootProductModel);
        $rootProductModel->getParent()->willReturn(null);

        $productModelIndexer->index($rootProductModel)->shouldBeCalled();
        $productModelIndexer->index($rootProductModel)->shouldBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event)->shouldReturn(null);
    }

    function it_does_not_compute_if_it_is_not_unitary_for_save(
        ProductModelIndexer $productModelIndexer
    ) {
        $productModelIndexer->index(Argument::any())->shouldNotBeCalled();

        $product = new Product();

        $this->computeNumberOfCompleteVariantProduct(new GenericEvent($product, ['unitary' => false]));
    }

    function it_indexes_multiple_products(
        ProductModelIndexer $productModelIndexer
    ) {
        $productA = new Product();
        $parent = new ProductModel();
        $productA->setParent($parent);

        $productModelIndexer->index($parent)->shouldBeCalled();
        $this->computeNumberOfCompleteVariantProducts(new GenericEvent([$productA]));
    }
}
