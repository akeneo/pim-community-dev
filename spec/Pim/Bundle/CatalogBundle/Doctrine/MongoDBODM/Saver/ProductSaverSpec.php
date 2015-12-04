<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\Versioning\BulkVersionBuilderInterface;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        DocumentManager $documentManager,
        CompletenessManager $completenessManager,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        BulkVersionBuilderInterface $bulkVersionBuilder,
        BulkSaverInterface $versionSaver,
        NormalizerInterface $normalizer,
        MongoObjectsFactory $mongoFactory,
        Collection $collection
    ) {
        $this->beConstructedWith(
            $documentManager,
            $completenessManager,
            $optionsResolver,
            $eventDispatcher,
            $bulkVersionBuilder,
            $versionSaver,
            $normalizer,
            $mongoFactory,
            'Pim\Bundle\CatalogBundle\Model\Product',
            'my_db'
        );

        $documentManager->getDocumentCollection('Pim\Bundle\CatalogBundle\Model\Product')->willReturn($collection);
        $collection->getName()->willReturn('pim_catalog_product');

        $bulkVersionBuilder->buildVersions(Argument::any())->willReturn([]);

        $optionsResolver
            ->resolveSaveAllOptions(Argument::any())
            ->willReturn(
                [
                    'flush'       => true,
                    'recalculate' => false,
                    'schedule'    => true
                ]
            );
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_inserts_or_updates_several_products(
        $versionSaver,
        $normalizer,
        $collection,
        ProductInterface $productA,
        ProductInterface $productB,
        ProductInterface $productC,
        ProductInterface $productD
    ) {
        $products = [$productA, $productB, $productC, $productD];

        $productA->getId()->willReturn('id_a');
        $productB->getId()->willReturn('id_b');
        $productC->getId()->willReturn(null);
        $productD->getId()->willReturn(null);

        $productA->setId(Argument::any())->shouldNotBeCalled();
        $productB->setId(Argument::any())->shouldNotBeCalled();
        $productC->setId(Argument::any())->shouldBeCalled();
        $productD->setId(Argument::any())->shouldBeCalled();

        $normalizer->normalize($productA, Argument::cetera())->willReturn(['_id' => 'id_a', 'key_a' => 'data_a']);
        $normalizer->normalize($productB, Argument::cetera())->willReturn(['_id' => 'id_b', 'key_b' => 'data_b']);
        $normalizer->normalize($productC, Argument::cetera())->willReturn(['_id' => 'id_c', 'key_c' => 'data_c']);
        $normalizer->normalize($productD, Argument::cetera())->willReturn(['_id' => 'id_d', 'key_d' => 'data_d']);

        $collection->batchInsert(
            [
                ['_id' => 'id_c', 'key_c' => 'data_c'],
                ['_id' => 'id_d', 'key_d' => 'data_d']
            ]
        )->shouldBeCalled();

        $collection->update(['_id' => 'id_a'], ['_id' => 'id_a', 'key_a' => 'data_a'])->shouldBeCalled();
        $collection->update(['_id' => 'id_b'], ['_id' => 'id_b', 'key_b' => 'data_b'])->shouldBeCalled();

        $versionSaver->saveAll(Argument::any())->shouldBeCalled();

        $this->saveAll($products);
    }

    function it_dispatches_events_on_save(
        $eventDispatcher,
        $collection,
        ProductInterface $productA,
        ProductInterface $productB
    ) {
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $collection->batchInsert(Argument::any())->willReturn(null);

        $this->saveAll([$productA, $productB]);
    }
}
