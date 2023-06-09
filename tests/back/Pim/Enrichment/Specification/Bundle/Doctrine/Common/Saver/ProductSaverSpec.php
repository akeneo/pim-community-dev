<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Automation\IdentifierGenerator\API\Query\UpdateIdentifierPrefixesQuery;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\UpdateIdentifierValuesQuery;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        Connection $connection,
    ) {
        $objectManager->getConnection()->willReturn($connection);
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            $uniqueDataSynchronizer,
            $updateIdentifierPrefixesQuery,
            $updateIdentifierValuesQuery,
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_saves_a_new_product(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        Connection $connection
    ) {
        $product->isDirty()->willReturn(true);
        $product->getCreated()->willReturn(null);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $connection->beginTransaction()->shouldBeCalled();
        $objectManager->persist($product)->shouldBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts([$product])->shouldBeCalled();
        $updateIdentifierValuesQuery->forProducts([$product])->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $connection->commit()->shouldBeCalled();

        $eventDispatcher->dispatch(
            new GenericEvent(
                $product->getWrappedObject(),
                ['unitary' => true, 'is_new' => true]
            ),
            StorageEvents::POST_SAVE
        )->shouldBeCalled();

        $product->cleanup()->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_an_existing_product(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        Connection $connection
    ) {
        $product->isDirty()->willReturn(true);
        $product->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $connection->beginTransaction()->shouldBeCalled();
        $objectManager->persist($product)->shouldBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts([$product])->shouldBeCalled();
        $updateIdentifierValuesQuery->forProducts([$product])->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $connection->commit()->shouldBeCalled();

        $eventDispatcher->dispatch(
            new GenericEvent(
                $product->getWrappedObject(),
                ['unitary' => true, 'is_new' => false]
            ),
            StorageEvents::POST_SAVE
        )->shouldBeCalled();
        $product->cleanup()->shouldBeCalled();

        $this->save($product);
    }

    function it_does_not_save_an_unchanged_product(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        ProductInterface $product,
    ) {
        $product->isDirty()->willReturn(false);

        $uniqueDataSynchronizer->synchronize($product)->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts(Argument::any())->shouldNotBeCalled();

        $this->save($product);
    }

    function it_saves_multiple_products(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        ProductInterface $product1,
        ProductInterface $product2,
        Connection $connection
    ) {
        $product1->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $product1->isDirty()->willReturn(true);
        $product2->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $product2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);

        $connection->beginTransaction()->shouldBeCalled();

        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalled();
        $objectManager->persist($product1)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product2)->shouldBeCalled();
        $objectManager->persist($product2)->shouldBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts([$product1, $product2])->shouldBeCalled();
        $updateIdentifierValuesQuery->forProducts([$product1, $product2])->shouldBeCalled();

        $connection->commit()->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();
        $product1->cleanup()->shouldBeCalled();
        $product2->cleanup()->shouldBeCalled();

        $this->saveAll([$product1, $product2]);
    }

    function it_throws_an_exception_when_trying_to_save_anything_but_a_product(
        EntityManagerInterface $objectManager
    ) {
        $otherObject = new \stdClass();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'Expects a Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface, "stdClass" provided'
                )
            )
            ->during('save', [$otherObject]);
    }

    function it_does_not_save_duplicate_products(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        ProductInterface $product1,
        ProductInterface $product2,
        Connection $connection,
    ) {
        $product1->getCreated()->willReturn(null);
        $product1->isDirty()->willReturn(true);
        $product2->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $product2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalledTimes(1);
        $uniqueDataSynchronizer->synchronize($product2)->shouldBeCalled();

        $connection->beginTransaction()->shouldBeCalled();

        $objectManager->persist($product1)->shouldBeCalledTimes(1);
        $objectManager->persist($product2)->shouldBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts([$product1, $product2])->shouldBeCalled();
        $updateIdentifierValuesQuery->forProducts([$product1, $product2])->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $connection->commit()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $product1->cleanup()->shouldBeCalled();
        $product2->cleanup()->shouldBeCalled();

        $this->saveAll([$product1, $product2, $product1]);
    }

    function it_only_saves_changed_products(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        Connection $connection,
    ) {
        $product1->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $product1->isDirty()->willReturn(true);
        $product2->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $product2->isDirty()->willReturn(false);
        $product3->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-28 12:12:12'));
        $product3->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);

        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product2)->shouldNotBeCalled();
        $uniqueDataSynchronizer->synchronize($product3)->shouldBeCalled();

        $connection->beginTransaction()->shouldBeCalled();

        $objectManager->persist($product1)->shouldBeCalled();
        $objectManager->persist($product2)->shouldNotBeCalled();
        $objectManager->persist($product3)->shouldBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts([$product1, $product3])->shouldBeCalled();
        $updateIdentifierValuesQuery->forProducts([$product1, $product3])->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $connection->commit()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $product1->cleanup()->shouldBeCalled();
        $product3->cleanup()->shouldBeCalled();

        $this->saveAll([$product1, $product2, $product3]);
    }

    function it_does_not_save_multiple_products_if_none_was_updated(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $product1->isDirty()->willReturn(false);
        $product2->isDirty()->willReturn(false);
        $product3->isDirty()->willReturn(false);

        $uniqueDataSynchronizer->synchronize(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts(Argument::any())->shouldNotBeCalled();
        $updateIdentifierValuesQuery->forProducts(Argument::any())->shouldNotBeCalled();

        $this->saveAll([$product1, $product2, $product3]);
    }

    function it_can_handle_an_empty_list(
        EntityManagerInterface $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        UpdateIdentifierPrefixesQuery $updateIdentifierPrefixesQuery,
        UpdateIdentifierValuesQuery $updateIdentifierValuesQuery,
    ) {
        $uniqueDataSynchronizer->synchronize(Argument::any())->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $updateIdentifierPrefixesQuery->updateFromProducts(Argument::any())->shouldNotBeCalled();
        $updateIdentifierValuesQuery->forProducts(Argument::any())->shouldNotBeCalled();

        $this->saveAll([]);
    }
}
