<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductModelSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_saves_a_new_product_model(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel
    ) {
        $productModel->isDirty()->willReturn(true);
        $productModel->getId()->willReturn(null);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $objectManager->persist($productModel)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            new GenericEvent(
                $productModel->getWrappedObject(),
                ['unitary' => true, 'is_new' => true]
            ),
            StorageEvents::POST_SAVE
        )->shouldBeCalled();

        $productModel->cleanup()->shouldBeCalled();

        $this->save($productModel);
    }

    function it_saves_an_existing_product_model(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel
    ) {
        $productModel->isDirty()->willReturn(true);
        $productModel->getId()->willReturn(1);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalled();
        $objectManager->persist($productModel)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            new GenericEvent(
                $productModel->getWrappedObject(),
                ['unitary' => true, 'is_new' => false]
            ),
            StorageEvents::POST_SAVE
        )->shouldBeCalled();
        $productModel->cleanup()->shouldBeCalled();

        $this->save($productModel);
    }

    function it_does_not_save_an_unchanged_product_model(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel
    ) {
        $productModel->isDirty()->willReturn(false);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class))->shouldNotBeCalled();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this->save($productModel);
    }

    function it_saves_multiple_product_models(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $productModel1->getId()->willReturn(42);
        $productModel1->isDirty()->willReturn(true);
        $productModel2->getId()->willReturn(44);
        $productModel2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);

        $objectManager->persist($productModel1)->shouldBeCalled();
        $objectManager->persist($productModel2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();
        $productModel1->cleanup()->shouldBeCalled();
        $productModel2->cleanup()->shouldBeCalled();

        $this->saveAll([$productModel1, $productModel2]);
    }

    function it_throws_an_exception_when_trying_to_save_anything_but_a_product_model(ObjectManager $objectManager)
    {
        $otherObject = new \stdClass();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'Expects a Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface, "stdClass" provided'
                )
            )
            ->during('save', [$otherObject]);
    }

    function it_does_not_save_duplicate_product_models(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $productModel1->getId()->willReturn(null);
        $productModel1->isDirty()->willReturn(true);
        $productModel2->getId()->willReturn(42);
        $productModel2->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $objectManager->persist($productModel1)->shouldBeCalledTimes(1);
        $objectManager->persist($productModel2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $productModel1->cleanup()->shouldBeCalled();
        $productModel2->cleanup()->shouldBeCalled();

        $this->saveAll([$productModel1, $productModel2, $productModel1]);
    }

    function it_only_saves_changed_product_models(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3
    ) {
        $productModel1->getId()->willReturn(1);
        $productModel1->isDirty()->willReturn(true);
        $productModel2->getId()->willReturn(2);
        $productModel2->isDirty()->willReturn(false);
        $productModel3->getId()->willReturn(3);
        $productModel3->isDirty()->willReturn(true);

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE_ALL)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::PRE_SAVE)->shouldBeCalledTimes(2);

        $objectManager->persist($productModel1)->shouldBeCalled();
        $objectManager->persist($productModel2)->shouldNotBeCalled();
        $objectManager->persist($productModel3)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE)->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), StorageEvents::POST_SAVE_ALL)->shouldBeCalled();

        $productModel1->cleanup()->shouldBeCalled();
        $productModel3->cleanup()->shouldBeCalled();

        $this->saveAll([$productModel1, $productModel2, $productModel3]);
    }

    function it_does_not_save_multiple_product_models_if_none_was_updated(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3
    ) {
        $productModel1->isDirty()->willReturn(false);
        $productModel2->isDirty()->willReturn(false);
        $productModel3->isDirty()->willReturn(false);

        $eventDispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->saveAll([$productModel1, $productModel2, $productModel3]);
    }
}
