<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\BulkVersionSaver;
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
        BulkVersionSaver $versionSaver,
        NormalizerInterface $normalizer,
        MongoObjectsFactory $mongoFactory,
        Collection $collection
    ) {
        $this->beConstructedWith(
            $documentManager,
            $completenessManager,
            $optionsResolver,
            $eventDispatcher,
            $versionSaver,
            $normalizer,
            $mongoFactory,
            'Pim\Bundle\CatalogBundle\Model\Product',
            'my_db'
        );

        $documentManager->getDocumentCollection('Pim\Bundle\CatalogBundle\Model\Product')->willReturn($collection);
        $collection->getName()->willReturn('pim_catalog_product');

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
        ProductInterface $product_a,
        ProductInterface $product_b,
        ProductInterface $product_c,
        ProductInterface $product_d
    ) {
        $products = [$product_a, $product_b, $product_c, $product_d];

        $product_a->getId()->willReturn('id_a');
        $product_b->getId()->willReturn('id_b');
        $product_c->getId()->willReturn(null);
        $product_d->getId()->willReturn(null);

        $product_c->setId(Argument::any())->shouldBeCalled();
        $product_d->setId(Argument::any())->shouldBeCalled();

        $normalizer->normalize($product_a, Argument::cetera())->willReturn(['_id' => 'id_a', 'key_a' => 'data_a']);
        $normalizer->normalize($product_b, Argument::cetera())->willReturn(['_id' => 'id_b', 'key_b' => 'data_b']);
        $normalizer->normalize($product_c, Argument::cetera())->willReturn(['_id' => 'id_c', 'key_c' => 'data_c']);
        $normalizer->normalize($product_d, Argument::cetera())->willReturn(['_id' => 'id_d', 'key_d' => 'data_d']);

        $collection->batchInsert(
            [
                ['_id' => 'id_c', 'key_c' => 'data_c'],
                ['_id' => 'id_d', 'key_d' => 'data_d']
            ]
        )->shouldBeCalled();

        $collection->update(['_id' => 'id_a'], ['_id' => 'id_a', 'key_a' => 'data_a'])->shouldBeCalled();
        $collection->update(['_id' => 'id_b'], ['_id' => 'id_b', 'key_b' => 'data_b'])->shouldBeCalled();

        $versionSaver->bulkSave($products)->shouldBeCalled();

        $this->saveAll($products);
    }

    function it_dispatches_events_on_save(
        $eventDispatcher,
        $collection,
        ProductInterface $product_a,
        ProductInterface $product_b
    ) {
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $collection->batchInsert(Argument::any())->willReturn(null);

        $this->saveAll([$product_a, $product_b]);
    }
}
