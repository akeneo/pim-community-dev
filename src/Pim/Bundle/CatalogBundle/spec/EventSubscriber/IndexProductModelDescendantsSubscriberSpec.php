<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\EventSubscriber\IndexProductModelDescendantsSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductModelDescendantsSubscriberSpec extends ObjectBehavior
{
    function let(
        IndexerInterface $indexer,
        BulkIndexerInterface $bulkIndexer,
        RemoverInterface $remover
    ) {
        $this->beConstructedWith($indexer, $bulkIndexer, $remover);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IndexProductModelDescendantsSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE     => 'indexProductModelDescendants',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProductModelsDescendants',
            StorageEvents::POST_REMOVE   => 'deleteProductModelDescendants',       ]);
    }

    function it_indexes_a_single_product_model_descendants($indexer, GenericEvent $event, ProductModelInterface $productModel)
    {
        $event->getSubject()->willReturn($productModel);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $productModel->getIdentifier()->willReturn('identifier');

        $indexer->index($productModel)->shouldBeCalled();

        $this->indexProductModelDescendants($event);
    }

    function it_bulk_indexes_product_models_descendants(
        $bulkIndexer,
        GenericEvent $event,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $event->getSubject()->willReturn([$productModel1, $productModel2]);

        $productModel1->getIdentifier()->willReturn('identifier1');
        $productModel2->getIdentifier()->willReturn('identifier2');

        $bulkIndexer->indexAll([$productModel1, $productModel2])->shouldBeCalled();

        $this->bulkIndexProductModelsDescendants($event);
    }

    function it_deletes_product_model_descendants_from_elasticsearch_index(
        $remover,
        RemoveEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubject()->willReturn($productModel);

        $remover->remove($productModel)->shouldBeCalled();

        $this->deleteProductModelDescendants($event)->shouldReturn(null);
    }

    function it_does_not_index_the_descendances_of_a_non_product_model_entity($indexer, GenericEvent $event, \stdClass $subject)
    {
        $event->getSubject()->willReturn($subject);
        $indexer->index(Argument::cetera())->shouldNotBeCalled();

        $this->indexProductModelDescendants($event);
    }

    function it_does_not_index_the_descendants_of_a_non_unitary_save_of_a_product_model(
        $indexer,
        GenericEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubject()->willReturn($productModel);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $indexer->index(Argument::any())->shouldNotBeCalled();

        $this->indexProductModelDescendants($event);
    }

    function it_does_not_index_the_descendants_of_a_non_unitary_save_of_a_product_model_bis(
        $indexer,
        GenericEvent $event,
        ProductModelInterface $productModel
    ) {
        $event->getSubject()->willReturn($productModel);
        $event->hasArgument('unitary')->willReturn(false);

        $indexer->index(Argument::any())->shouldNotBeCalled();

        $this->indexProductModelDescendants($event);
    }

    function it_does_not_bulk_index_the_descendants_of_a_non_product_model_entities(
        $bulkIndexer,
        GenericEvent $event,
        \stdClass $subject1
    ) {
        $event->getSubject()->willReturn([$subject1]);

        $bulkIndexer->indexAll(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProductModelsDescendants($event);
    }

    function it_does_not_bulk_index_non_collections($bulkIndexer, GenericEvent $event, \stdClass $subject1)
    {
        $event->getSubject()->willReturn($subject1);

        $bulkIndexer->indexAll(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProductModelsDescendants($event);
    }

    function it_does_not_delete_non_product_model_entity_from_elasticsearch($remover, RemoveEvent $event, \stdClass $subject)
    {
        $event->getSubject()->willReturn($subject);

        $remover->remove(40)->shouldNotBeCalled();

        $this->deleteProductModelDescendants($event)->shouldReturn(null);
    }
}
