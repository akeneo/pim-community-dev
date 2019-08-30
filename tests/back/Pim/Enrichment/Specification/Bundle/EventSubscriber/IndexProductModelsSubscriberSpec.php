<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
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
            StorageEvents::POST_SAVE => 'indexProductModel',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProductModels',
            StorageEvents::POST_REMOVE => 'deleteProductModel',
        ]);
    }

    function it_indexes_a_single_product_model($productModelIndexer, GenericEvent $event, ProductModelInterface $productModel)
    {
        $event->getSubject()->willReturn($productModel);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $productModel->getCode()->willReturn('identifier');

        $productModelIndexer->indexFromProductModelCode('identifier')->shouldBeCalled();

        $this->indexProductModel($event);
    }

    function it_bulk_indexes_products(
        $productModelIndexer,
        GenericEvent $event,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $event->getSubject()->willReturn([$productModel1, $productModel2]);

        $productModel1->getCode()->willReturn('identifier1');
        $productModel2->getCode()->willReturn('identifier2');

        $productModelIndexer->indexFromProductModelCodes(['identifier1', 'identifier2'])->shouldBeCalled();

        $this->bulkIndexProductModels($event);
    }

    function it_deletes_a_product_model_from_elasticsearch_index(
        $productModelIndexer,
        RemoveEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubjectId()->willReturn(40);
        $event->getSubject()->willReturn($productModel);

        $productModelIndexer->removeFromProductModelId(40)->shouldBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }

    function it_does_not_index_a_non_product_model_entity($productModelIndexer, GenericEvent $event, \stdClass $subject)
    {
        $event->getSubject()->willReturn($subject);
        $productModelIndexer->indexFromProductModelCode(Argument::cetera())->shouldNotBeCalled();

        $this->indexProductModel($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_model(
        $productModelIndexer,
        GenericEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubject()->willReturn($productModel);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $productModelIndexer->indexFromProductModelCode(Argument::any())->shouldNotBeCalled();

        $this->indexProductModel($event);
    }

    function it_does_not_index_a_non_unitary_save_of_a_product_model_bis(
        $productModelIndexer,
        GenericEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubject()->willReturn($productModel);
        $event->hasArgument('unitary')->willReturn(false);

        $productModelIndexer->indexFromProductModelCode(Argument::any())->shouldNotBeCalled();

        $this->indexProductModel($event);
    }

    function it_does_not_bulk_index_non_product_model_entities(
        $productModelIndexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $productModelIndexer->indexFromProductModelCodes(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProductModels($event);
    }

    function it_does_not_bulk_index_non_collections($productModelIndexer, GenericEvent $event, \stdClass $subject1)
    {
        $event->getSubject()->willReturn($subject1);

        $productModelIndexer->indexFromProductModelCodes(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProductModels($event);
    }

    function it_does_not_delete_non_product_model_entity_from_elasticsearch(
        $productModelIndexer,
        RemoveEvent $event,
        \stdClass $subject
    ) {
        $event->getSubject()->willReturn($subject);

        $productModelIndexer->removeFromProductModelId(40)->shouldNotBeCalled();

        $this->deleteProductModel($event)->shouldReturn(null);
    }
}
