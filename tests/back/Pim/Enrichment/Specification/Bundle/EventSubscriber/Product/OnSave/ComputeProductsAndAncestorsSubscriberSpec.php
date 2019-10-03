<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ComputeProductsAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeProductsAndAncestorsSubscriberSpec extends ObjectBehavior
{
    function let(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $this->beConstructedWith($computeAndPersistProductCompletenesses, $productAndAncestorsIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeProductsAndAncestorsSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_save_events()
    {
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE);
        $this::getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_SAVE_ALL);
    }

    function it_only_handles_products(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::any())->shouldNotBeCalled();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->handleSingleProduct(new GenericEvent(new \stdClass(), ['unitary' => true]));
        $this->handleMultipleProducts(new GenericEvent([new \stdClass()]));
    }

    function it_does_not_handle_single_products_on_non_unitary_save(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::any())->shouldNotBeCalled();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->handleSingleProduct(new GenericEvent(new \stdClass(), ['unitary' => false]));
        $this->handleSingleProduct(new GenericEvent([new \stdClass()]));
    }

    function it_computes_completeness_and_reindexes_a_product_and_its_ancestors(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['foo'])->shouldBeCalled();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(['foo'])->shouldBeCalled();

        $product = new Product();
        $product->setIdentifier('foo');
        $this->handleSingleProduct(new GenericEvent($product, ['unitary' => true]));
    }

    function it_computes_completeness_and_reindexes_multiple_products_and_their_ancestors(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['foo', 'bar'])->shouldBeCalled();
        $productAndAncestorsIndexer->indexFromProductIdentifiers(['foo', 'bar'])->shouldBeCalled();

        $product = new Product();
        $product->setIdentifier('foo');
        $otherProduct = new Product();
        $otherProduct->setIdentifier('bar');

        $this->handleMultipleProducts(new GenericEvent([$product, $otherProduct], ['unitary' => false]));
    }
}
