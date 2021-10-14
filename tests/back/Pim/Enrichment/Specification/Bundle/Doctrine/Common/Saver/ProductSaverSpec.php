<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher, $uniqueDataSynchronizer);
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
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product
    ) {
        $product->isDirty()->willReturn(true);
        $product->getId()->willReturn(null);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $objectManager->persist($product)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

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
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product
    ) {
        $product->isDirty()->willReturn(true);
        $product->getId()->willReturn(1);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $objectManager->persist($product)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

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
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product
    ) {
        $product->isDirty()->willReturn(false);

        $uniqueDataSynchronizer->synchronize($product)->shouldNotBeCalled();
        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this->save($product);
    }

    function it_saves_multiple_products(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn(42);
        $product1->isDirty()->willReturn(true);
        $product2->getId()->willReturn(44);
        $product2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);

        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalled();
        $objectManager->persist($product1)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product2)->shouldBeCalled();
        $objectManager->persist($product2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();
        $product1->cleanup()->shouldBeCalled();
        $product2->cleanup()->shouldBeCalled();

        $this->saveAll([$product1, $product2]);
    }

    function it_throws_an_exception_when_trying_to_save_anything_but_a_product(ObjectManager $objectManager)
    {
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
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getId()->willReturn(null);
        $product1->isDirty()->willReturn(true);
        $product2->getId()->willReturn(42);
        $product2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalledTimes(1);
        $uniqueDataSynchronizer->synchronize($product2)->shouldBeCalled();

        $objectManager->persist($product1)->shouldBeCalledTimes(1);
        $objectManager->persist($product2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $product1->cleanup()->shouldBeCalled();
        $product2->cleanup()->shouldBeCalled();

        $this->saveAll([$product1, $product2, $product1]);
    }

    function it_only_saves_changed_products(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $product1->getId()->willReturn(1);
        $product1->isDirty()->willReturn(true);
        $product2->getId()->willReturn(2);
        $product2->isDirty()->willReturn(false);
        $product3->getId()->willReturn(3);
        $product3->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);

        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product2)->shouldNotBeCalled();
        $uniqueDataSynchronizer->synchronize($product3)->shouldBeCalled();

        $objectManager->persist($product1)->shouldBeCalled();
        $objectManager->persist($product2)->shouldNotBeCalled();
        $objectManager->persist($product3)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $product1->cleanup()->shouldBeCalled();
        $product3->cleanup()->shouldBeCalled();

        $this->saveAll([$product1, $product2, $product3]);
    }

    function it_does_not_save_multiple_products_if_none_was_updated(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
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

        $this->saveAll([$product1, $product2, $product3]);
    }
}
