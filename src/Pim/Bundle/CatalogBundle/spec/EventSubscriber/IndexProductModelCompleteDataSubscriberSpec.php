<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\EventSubscriber\IndexProductModelCompleteDataSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
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
            StorageEvents::POST_SAVE => 'computeNumberOfCompleteVariantProduct'
        ]);
    }

    function it_only_works_with_variant_product(
        $productModelIndexer,
        GenericEvent $event,
        ProductModelInterface $productModel,
        VariantProductInterface $product
    ) {
        $event->getSubject()->willReturn($productModel);
        $productModelIndexer->index(Argument::any())->shouldNotBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event);

        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);
        $product->getParent()->willReturn(null);
        $productModelIndexer->index(Argument::any())->shouldNotBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event);
    }

    function it_computes_number_of_complete_variant_product_with_one_level(
        $productModelIndexer,
        GenericEvent $event,
        VariantProductInterface $product,
        ProductModelInterface $rootProductModel
    ) {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);
        $product->getParent()->willReturn($rootProductModel);
        $rootProductModel->getParent()->willReturn(null);

        $productModelIndexer->index($rootProductModel)->shouldBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event)->shouldReturn(null);
    }

    function it_computes_number_of_complete_variant_product_with_two_level(
        $productModelIndexer,
        GenericEvent $event,
        VariantProductInterface $product,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $subProductModel
    ) {
        $event->getSubject()->willReturn($product);
        $product->isVariant()->willReturn(true);
        $product->getParent()->willReturn($subProductModel);
        $subProductModel->getParent()->willReturn(null);
        $product->getParent()->willReturn($rootProductModel);
        $rootProductModel->getParent()->willReturn(null);

        $productModelIndexer->index($rootProductModel)->shouldBeCalled();
        $productModelIndexer->index($rootProductModel)->shouldBeCalled();
        $this->computeNumberOfCompleteVariantProduct($event)->shouldReturn(null);
    }
}
