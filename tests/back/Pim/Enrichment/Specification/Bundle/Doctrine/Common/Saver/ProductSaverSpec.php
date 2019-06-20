<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyOfProductChanged;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueAdded;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $objectManager,
            $completenessManager,
            $eventDispatcher,
            $uniqueDataSynchronizer,
            $attributeRepository
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

    function it_saves_a_product_without_completeless_when_no_events(
        $completenessManager,
        ProductInterface $product,
        FamilyInterface $family
    ) {

        $product->popEvents()->willReturn([]);
        $product->getFamily()->willReturn($family);

        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $this->save($product);
    }

    function it_saves_a_product_with_completeless_when_family_changed(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $uniqueDataSynchronizer,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $product->popEvents()->willReturn([new FamilyOfProductChanged('familyCode', 'newFamily')]);
        $product->getFamily()->willReturn($family);
        $product->getIdentifier()->willReturn('productIdentifier');

//        $objectManager->persist($product)->shouldBeCalled();
//        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();
//        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_a_product_without_synchronization_when_value_added(
        $uniqueDataSynchronizer,
        ProductInterface $product,
        FamilyInterface $family
    ) {

        $product->popEvents()->willReturn([]);
        $product->getFamily()->willReturn($family);

        $uniqueDataSynchronizer->synchronize($product)->shouldNotBeCalled();

        $this->save($product);
    }

    function it_saves_a_product_with_synchronization_when_no_events(
        $uniqueDataSynchronizer,
        $attributeRepository,
        ProductInterface $product,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {

        $product->popEvents()->willReturn([new ValueAdded('attributeCode', null, null)]);
        $product->getFamily()->willReturn($family);
        $product->getIdentifier()->willReturn('productIdentifier');
        $attributeRepository->findOneByIdentifier('attributeCode')->shouldBeCalled()->willReturn($attribute);
        $attribute->isUnique()->willReturn(true);

        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_multiple_products_with_completeness_and_synchronization(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $uniqueDataSynchronizer,
        $attributeRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        FamilyInterface $family,
        AttributeInterface $attribute
    ) {
        $product1->popEvents()->willReturn([
            new FamilyOfProductChanged('familyCode', 'newFamily'),
            new ValueAdded('attributeCode', null, null)
        ]);
        $product1->getFamily()->willReturn($family);
        $product1->getIdentifier()->willReturn('productIdentifier1');

        $product2->popEvents()->willReturn([
            new FamilyOfProductChanged('familyCode', 'newFamily'),
            new ValueAdded('attributeCode', null, null)
        ]);
        $product2->getFamily()->willReturn($family);
        $product2->getIdentifier()->willReturn('productIdentifier2');

        $attributeRepository->findOneByIdentifier('attributeCode')->shouldBeCalled()->willReturn($attribute);
        $attribute->isUnique()->willReturn(true);

        $completenessManager->generateMissingForProduct($product1)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product2)->shouldBeCalled();

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
            ->shouldThrow(new \InvalidArgumentException('Expects a Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface, "stdClass" provided'))
            ->duringSave($otherObject);
    }

    function it_does_not_save_duplicate_products(
        $objectManager,
        $eventDispatcher,
        ProductInterface $product1,
        ProductInterface $product2,
        FamilyInterface $family
    ) {
        $product1->popEvents()->willReturn([]);
        $product2->popEvents()->willReturn([]);
        $product1->getFamily()->willReturn($family);
        $product2->getFamily()->willReturn($family);

        $objectManager->persist($product1)->shouldBeCalledTimes(1);
        $objectManager->persist($product2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalledTimes(2);

        $this->saveAll([$product1, $product2, $product1]);
    }
}
