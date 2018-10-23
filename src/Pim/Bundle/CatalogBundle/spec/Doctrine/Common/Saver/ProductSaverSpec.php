<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($objectManager, $completenessManager, $eventDispatcher, $uniqueDataSynchronizer, $entityManager);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_saves_a_product_after_droping_its_previous_completenesses(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $uniqueDataSynchronizer,
        ProductInterface $product
    ) {
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $product->getCompletenesses()->willReturn(new ArrayCollection());

        $objectManager->persist($product)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_multiple_products_after_droping_their_previous_completenesses(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $uniqueDataSynchronizer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $completenessManager->schedule($product1)->shouldBeCalled();
        $completenessManager->schedule($product2)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product1)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product2)->shouldBeCalled();

        $product1->getCompletenesses()->willReturn(new ArrayCollection());
        $product2->getCompletenesses()->willReturn(new ArrayCollection());

        $objectManager->persist($product1)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalled();
        $objectManager->persist($product2)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalledTimes(2);

        $this->saveAll([$product1, $product2]);
    }

    function it_throws_an_exception_when_try_to_save_something_else_than_a_product(
        $objectManager
    ) {
        $otherObject = new \stdClass();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Expects a Pim\Component\Catalog\Model\ProductInterface, "stdClass" provided'))
            ->duringSave($otherObject);
    }

    function it_does_not_save_duplicate_products(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $uniqueDataSynchronizer,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $completenessManager->schedule($product1)->shouldBeCalledTimes(1);
        $completenessManager->schedule($product2)->shouldBeCalled();

        $completenessManager->generateMissingForProduct($product1)->shouldBeCalledTimes(1);
        $completenessManager->generateMissingForProduct($product2)->shouldBeCalled();

        $product1->getCompletenesses()->willReturn(new ArrayCollection());
        $product2->getCompletenesses()->willReturn(new ArrayCollection());

        $objectManager->persist($product1)->shouldBeCalledTimes(1);
        $uniqueDataSynchronizer->synchronize($product1)->shouldBeCalledTimes(1);

        $objectManager->persist($product2)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalledTimes(2);

        $this->saveAll([$product1, $product2, $product1]);
    }
}
