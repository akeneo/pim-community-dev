<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ProductSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, CompletenessManager $completenessManager)
    {
        $this->beConstructedWith($objectManager, $completenessManager);
    }

    function it_persists_flushes_schedule_and_recalculate_completeness_of_products_in_database(
        $objectManager,
        CompletenessManager $completenessManager,
        ProductInterface $product
    ) {
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_does_not_schedule_neither_recalculate_completeness_when_persisting(
        $objectManager,
        CompletenessManager $completenessManager,
        ProductInterface $product
    ) {
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldNotBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $this->save($product, ['recalculate' => false, 'flush' => true, 'schedule' => false]);
    }

    function it_does_not_flush_object_manager_when_persisting(
        $objectManager,
        CompletenessManager $completenessManager,
        ProductInterface $product
    ) {
        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

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

    function it_throws_an_exception_when_unknown_saving_option_is_used(
        $objectManager,
        ProductInterface $product
    ) {
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new InvalidOptionsException('The option "fake_option" does not exist. Known options are: "flush", "recalculate", "schedule"'))
            ->duringSave($product, ['fake_option' => true, 'recalculate' => false, 'flush' => false, 'schedule' => true]);
    }
}
