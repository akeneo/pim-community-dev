<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductModelsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductModelsSubscriberSpec extends ObjectBehavior
{
    function let(ProductModelIndexerInterface $productModelIndexer)
    {
        $this->beConstructedWith($productModelIndexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductModelsSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_REMOVE => 'deleteProductModel',
            StorageEvents::POST_REMOVE_ALL => 'bulkDeleteProductModels',
        ]);
    }

    function it_deletes_a_product_model_from_elasticsearch_index(
        $productModelIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent($productModel->getWrappedObject(), 40, ['unitary' => true]);

        $productModelIndexer->removeFromProductModelId(40)->shouldBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }

    function it_does_not_delete_non_product_model_entity_from_elasticsearch($productModelIndexer)
    {
        $event = new RemoveEvent(new \stdClass(), 40, ['unitary' => true]);

        $productModelIndexer->removeFromProductModelId(40)->shouldNotBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }

    function it_does_not_delete_product_model_entity_if_not_unitary_operation(
        $productModelIndexer,
        ProductModelInterface $productModel
    ) {
        $event = new RemoveEvent($productModel->getWrappedObject(), null, ['unitary' => false]);

        $productModelIndexer->removeFromProductModelId(Argument::cetera())->shouldNotBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }
}
