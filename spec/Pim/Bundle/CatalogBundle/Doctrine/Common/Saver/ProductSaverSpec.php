<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        ProductSavingOptionsResolver $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $completenessManager, $optionsResolver, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_persists_flushes_schedule_and_recalculate_completeness_of_products_in_database(
        $objectManager,
        $completenessManager,
        $optionsResolver,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_does_not_schedule_neither_recalculate_completeness_when_persisting(
        $objectManager,
        $completenessManager,
        $optionsResolver,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => false, 'flush' => true, 'schedule' => false])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => false, 'flush' => true, 'schedule' => false]);
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldNotBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product, ['recalculate' => false, 'flush' => true, 'schedule' => false]);
    }

    function it_does_not_flush_object_manager_when_persisting(
        $objectManager,
        $completenessManager,
        $optionsResolver,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => false, 'flush' => false, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => false, 'flush' => false, 'schedule' => true]);
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product, ['recalculate' => false, 'flush' => false, 'schedule' => true]);
    }

    function it_throws_an_exception_when_try_to_save_something_else_than_a_product(
        $objectManager
    ) {
        $otherObject = new \stdClass();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "stdClass" provided'))
            ->duringSave($otherObject, ['recalculate' => false, 'flush' => false, 'schedule' => true]);
    }
}
