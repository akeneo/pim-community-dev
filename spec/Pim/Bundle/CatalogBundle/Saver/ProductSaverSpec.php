<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $om, CompletenessManager $completenessManager)
    {
        $this->beConstructedWith($om, $completenessManager);
    }

    function it_persists_flushes_schedule_and_recalculate_completeness_of_products_in_database(
        $om,
        CompletenessManager $completenessManager,
        ProductInterface $product
    ) {
        $om->persist($product)->shouldBeCalled();
        $om->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_does_not_schedule_neither_recalculate_completeness_when_persisting(
        $om,
        CompletenessManager $completenessManager,
        ProductInterface $product
    ) {
        $om->persist($product)->shouldBeCalled();
        $om->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldNotBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $this->save($product, ['recalculate' => false, 'flush' => true, 'schedule' => false]);
    }

    function it_does_not_flush_object_manager_when_persisting(
        $om,
        CompletenessManager $completenessManager,
        ProductInterface $product
    ) {
        $om->persist($product)->shouldBeCalled();
        $om->flush()->shouldNotBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $this->save($product, ['recalculate' => false, 'flush' => false, 'schedule' => true]);
    }
}
