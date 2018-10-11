<?php

declare(strict_types=1);

namespace Specification\Akeneo\Asset\Bundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class AssetUpdatedAtSubscriberSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_updates_an_asset_if_a_new_reference_is_saved(
        $entityManager,
        GenericEvent $event,
        ReferenceInterface $reference,
        AssetInterface $asset
    ) {
        $event->getSubject()->willReturn($reference);
        $reference->getAsset()->willReturn($asset);

        $this->updateAsset($event);

        $asset->setUpdatedAt(Argument::type(\DateTime::class))->shouldHaveBeenCalled();
        $entityManager->persist($asset)->shouldHaveBeenCalled();
    }

    function it_updates_an_asset_if_a_new_variation_is_saved(
        $entityManager,
        GenericEvent $event,
        VariationInterface $variation,
        AssetInterface $asset
    ) {
        $event->getSubject()->willReturn($variation);
        $variation->getAsset()->willReturn($asset);

        $this->updateAsset($event);

        $asset->setUpdatedAt(Argument::type(\DateTime::class))->shouldHaveBeenCalled();
        $entityManager->persist($asset)->shouldHaveBeenCalled();
    }

    function it_does_not_update_an_asset_if_no_reference_nor_variation_file_were_saved(
        $entityManager,
        GenericEvent $event,
        \stdClass $object
    ) {
        $event->getSubject()->willReturn($object);

        $this->updateAsset($event);

        $entityManager->persist(Argument::any())->shouldNotHaveBeenCalled();
    }
}
