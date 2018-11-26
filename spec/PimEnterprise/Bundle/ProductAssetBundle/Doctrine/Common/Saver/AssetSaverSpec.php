<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
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
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
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
                'Expects a "PimEnterprise\Component\ProductAsset\Model\AssetInterface", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
