<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\PendingMassPersister;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class ProductWriterSpec extends ObjectBehavior
{
    function let(
        DocumentManager $documentManager,
        PendingMassPersister $pendingPersister,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher,
        MongoObjectsFactory $mongoFactory,
        StepExecution $stepExecution,
        Collection $collection,
        CacheClearer $clearer
    ) {
        $documentManager->getDocumentCollection('pim_product')->willReturn($collection);
        $collection->getName()->willReturn('pim_product_collection');
        $this->beConstructedWith(
            $documentManager,
            $pendingPersister,
            $normalizer,
            $eventDispatcher,
            $mongoFactory,
            'pim_product',
            $clearer
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_reader()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_massively_insert_products(
        $documentManager,
        $collection,
        $normalizer,
        $mongoFactory,
        $pendingPersister,
        $eventDispatcher,
        $mediaManager,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $mongoFactory->createMongoId()->willReturn('my_mongo_id');
        $product1->getId()->willReturn(null);
        $product2->getId()->willReturn(null);
        $product1->setId('my_mongo_id')->shouldBeCalled();
        $product2->setId('my_mongo_id')->shouldBeCalled();

        $normalizer->normalize(
            $product1,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['normalized_product_1']);

        $normalizer->normalize(
            $product2,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['normalized_product_2']);

        $collection->batchInsert([['normalized_product_1'], ['normalized_product_2']])->shouldBeCalled();
        $collection->update(Argument::cetera())->shouldNotBeCalled();
        $collection->update(Argument::cetera())->shouldNotBeCalled();

        $pendingPersister->persistPendingVersions([$product1, $product2])->shouldBeCalled();

        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.pre_insert', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.pre_update', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.post_insert', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.post_update', Argument::any())
            ->shouldBeCalled();

        $documentManager->clear()->shouldBeCalled();
        $this->write([$product1, $product2]);
    }

    function it_update_products(
        $documentManager,
        $collection,
        $normalizer,
        $mongoFactory,
        $pendingPersister,
        $eventDispatcher,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $mongoFactory->createMongoId()->willReturn('my_mongo_id');
        $product1->getId()->willReturn("my_product_1");
        $product2->getId()->willReturn("my_product_2");
        $product1->setId(Argument::any())->shouldNotBeCalled();
        $product2->setId(Argument::any())->shouldNotBeCalled();

        $normalizer->normalize(
            $product1,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['_id' => 'my_product_1', 'normalized_product_1']);

        $normalizer->normalize(
            $product2,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['_id' => 'my_product_2', 'normalized_product_2']);

        $collection->batchInsert(Argument::any())->shouldNotBeCalled();
        $collection->update(
            ['_id' => 'my_product_1'],
            ['_id' => 'my_product_1', 'normalized_product_1']
        )->shouldBeCalled();

        $collection->update(
            ['_id' => 'my_product_2'],
            ['_id' => 'my_product_2', 'normalized_product_2']
        )->shouldBeCalled();

        $pendingPersister->persistPendingVersions([$product1, $product2])->shouldBeCalled();

        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.pre_insert', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.pre_update', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.post_insert', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.post_update', Argument::any())
            ->shouldBeCalled();

        $documentManager->clear()->shouldBeCalled();
        $this->write([$product1, $product2]);
    }

    function it_massively_insert_new_products_and_update_existing_products(
        $documentManager,
        $collection,
        $normalizer,
        $mongoFactory,
        $pendingPersister,
        $eventDispatcher,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4
    ) {
        $mongoFactory->createMongoId()->willReturn('my_mongo_id');
        $product1->getId()->willReturn("my_product_1");
        $product2->getId()->willReturn(null);
        $product3->getId()->willReturn("my_product_3");
        $product4->getId()->willReturn(null);

        $product1->setId(Argument::any())->shouldNotBeCalled();
        $product2->setId('my_mongo_id')->shouldBeCalled();
        $product3->setId(Argument::any())->shouldNotBeCalled();
        $product4->setId('my_mongo_id')->shouldBeCalled();

        $normalizer->normalize(
            $product1,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['_id' => 'my_product_1', 'normalized_product_1']);

        $normalizer->normalize(
            $product2,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['_id' => 'my_mongo_id', 'normalized_product_2']);

        $normalizer->normalize(
            $product3,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['_id' => 'my_product_3', 'normalized_product_3']);

        $normalizer->normalize(
            $product4,
            'mongodb_document',
            ['collection_name' => 'pim_product_collection']
        )->willReturn(['_id' => 'my_mongo_id', 'normalized_product_4']);

        $collection->batchInsert([
            ['_id' => 'my_mongo_id', 'normalized_product_2'],
            ['_id' => 'my_mongo_id', 'normalized_product_4']
        ])->shouldBeCalled();

        $collection->update(
            ['_id' => 'my_product_1'],
            ['_id' => 'my_product_1', 'normalized_product_1']
        )->shouldBeCalled();

        $collection->update(
            ['_id' => 'my_product_3'],
            ['_id' => 'my_product_3', 'normalized_product_3']
        )->shouldBeCalled();

        $pendingPersister->persistPendingVersions([$product1, $product2, $product3, $product4])->shouldBeCalled();

        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.pre_insert', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.pre_update', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.post_insert', Argument::any())
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch('pim_base_connector.direct_to_db_writer.post_update', Argument::any())
            ->shouldBeCalled();

        $documentManager->clear()->shouldBeCalled();
        $this->write([$product1, $product2, $product3, $product4]);
    }
}
