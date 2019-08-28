<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\IndexProductsSubscriber;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class IndexProductsSubscriberSpec
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsSubscriberSpec extends ObjectBehavior
{
    function let(ProductIndexerInterface $productIndexer, PublishedProductIndexer $publishedProductIndexer)
    {
        $this->beConstructedWith($productIndexer, $publishedProductIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductsSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE     => ['indexProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProducts', 300],
            StorageEvents::PRE_REMOVE   => ['deleteProduct', 300],
        ]);
    }

    function it_indexes_a_single_published_product(
        $publishedProductIndexer,
        $productIndexer,
        GenericEvent $event,
        PublishedProduct $publishedProduct
    ) {
        $event->getSubject()->willReturn($publishedProduct);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $publishedProductIndexer->index($publishedProduct)->shouldBeCalled();
        $productIndexer->indexFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_indexes_a_single_product(
        $publishedProductIndexer,
        $productIndexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $product->getIdentifier()->willReturn('identifier');
        $publishedProductIndexer->index(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifier('identifier')->shouldBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product(
        $publishedProductIndexer,
        $productIndexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $publishedProductIndexer->index(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_bis(
        $publishedProductIndexer,
        $productIndexer,
        GenericEvent $event,
        ProductInterface $product
    ) {
        $event->getSubject()->willReturn($product);
        $event->hasArgument('unitary')->willReturn(false);

        $publishedProductIndexer->index(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->indexProduct($event);
    }

    function it_does_not_index_a_non_product_entity($productIndexer, $publishedProductIndexer)
    {
        $publishedProductIndexer->index(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifier(Argument::cetera())->shouldNotBeCalled();

        $this->indexProduct(new GenericEvent(new \stdClass()));
    }

    function it_indexes_several_products(
        $publishedProductIndexer,
        $productIndexer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $event = new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()]);
        $product1->getIdentifier()->willReturn('foo');
        $product2->getIdentifier()->willReturn('bar');

        $publishedProductIndexer->indexAll(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar'])->shouldBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_indexes_several_published_products(
        $publishedProductIndexer,
        $productIndexer,
        PublishedProductInterface $product1,
        PublishedProductInterface $product2
    ) {
        $event = new GenericEvent([$product1->getWrappedObject(), $product2->getWrappedObject()]);

        $publishedProductIndexer->indexAll([$product1, $product2])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_does_not_bulk_index_non_product_entities(
        $publishedProductIndexer,
        $productIndexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $publishedProductIndexer->indexAll(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts($event);
    }

    function it_does_not_bulk_index_non_collections($publishedProductIndexer, $productIndexer)
    {
        $publishedProductIndexer->indexAll(Argument::any())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProducts(new GenericEvent(new \stdClass()));
    }

    function it_delete_product_from_elasticsearch_index(
        $publishedProductIndexer,
        $productIndexer,
        ProductInterface $product
    ) {
        $event = new RemoveEvent($product->getWrappedObject(), 40);
        $product->getIdentifier()->willReturn('40');

        $publishedProductIndexer->remove(Argument::any())->shouldNotBeCalled();
        $productIndexer->removeFromProductIdentifier('40')->shouldBeCalled();

        $this->deleteProduct($event)->shouldReturn(null);
    }

    function it_delete_published_product_from_elasticsearch_index(
        $publishedProductIndexer,
        $productIndexer,
        PublishedProductInterface $product
    ) {
        $event = new RemoveEvent($product->getWrappedObject(), 40);

        $publishedProductIndexer->remove(40)->shouldBeCalled();
        $productIndexer->removeFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->deleteProduct($event)->shouldReturn(null);
    }

    function it_does_not_delete_a_non_product_entity_from_elastisearch(
        $publishedProductIndexer,
        $productIndexer,
        PublishedProductInterface $product
    ) {
        $event = new RemoveEvent(new \stdClass(), 1);

        $publishedProductIndexer->remove(Argument::any())->shouldNotBeCalled();
        $productIndexer->removeFromProductIdentifier(Argument::any())->shouldNotBeCalled();

        $this->deleteProduct($event)->shouldReturn(null);
    }
}
