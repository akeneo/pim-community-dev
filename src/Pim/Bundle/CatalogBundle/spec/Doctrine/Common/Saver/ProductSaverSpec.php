<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $completenessManager, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_saves_a_product_and_schedule_completeness_in_database(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_multiple_products_and_schedule_completeness_in_database(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $objectManager->persist($product1)->shouldBeCalled();
        $objectManager->persist($product2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $completenessManager->schedule($product1)->shouldBeCalled();
        $completenessManager->schedule($product2)->shouldBeCalled();

        $completenessManager->generateMissingForProduct($product1)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product2)->shouldBeCalled();

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
}
