<?php

namespace spec\Pim\Bundle\CatalogBundle\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

class BasicPersisterSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, CompletenessManager $completenessManager, VersionManager $versionManager)
    {
        $this->beConstructedWith($registry, $completenessManager, $versionManager);
    }

    function it_persists_flushes_schedule_and_recalculate_completeness_of_products_in_database(
        ManagerRegistry $registry,
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        ProductInterface $product,
        VersionManager $versionManager
    ) {
        $registry->getManagerForClass(get_class($product->getWrappedObject()))->willReturn($objectManager);

        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $versionManager->buildVersions($product, [])->willReturn([]);
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $this->persist($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_does_not_schedule_neither_recalculate_completeness_when_persisting(
        ManagerRegistry $registry,
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        ProductInterface $product,
        VersionManager $versionManager
    ) {
        $registry->getManagerForClass(get_class($product->getWrappedObject()))->willReturn($objectManager);

        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldNotBeCalled();
        $versionManager->buildVersions($product, [])->willReturn([]);
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $this->persist($product, ['recalculate' => false, 'flush' => true, 'schedule' => false]);
    }

    function it_does_not_flush_object_manager_when_persisting(
        ManagerRegistry $registry,
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        ProductInterface $product,
        VersionManager $versionManager
    ) {
        $registry->getManagerForClass(get_class($product->getWrappedObject()))->willReturn($objectManager);

        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $versionManager->buildVersions($product, [])->willReturn([]);
        $completenessManager->generateMissingForProduct($product)->shouldNotBeCalled();

        $this->persist($product, ['recalculate' => false, 'flush' => false, 'schedule' => true]);
    }
}
