<?php

namespace Specification\Akeneo\Asset\Bundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\AssetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetSaverSpec extends ObjectBehavior
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
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_persists_the_asset_and_flushes_the_unit_of_work(
        $objectManager,
        $eventDispatcher,
        AssetInterface $asset
    ) {
        $objectManager->persist($asset)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($asset);
    }

    function it_persists_the_assets_and_flushes_the_unit_of_work(
        $objectManager,
        $eventDispatcher,
        AssetInterface $asset1,
        AssetInterface $asset2
    ) {
        $objectManager->persist($asset1)->shouldBeCalled();
        $objectManager->persist($asset2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->saveAll([$asset1, $asset2]);
    }

    function it_throws_exception_when_save_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                AssetInterface::class,
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
