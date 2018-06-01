<?php

namespace spec\Akeneo\Asset\Bundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Akeneo\Asset\Component\Completeness\CompletenessRemoverInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CompletenessRemoverInterface $completenessRemover
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher, $completenessRemover);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface');
        $this->shouldHaveType('Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_persists_the_asset_and_flushes_the_unit_of_work_and_schedule_completeness(
        $objectManager,
        $eventDispatcher,
        $completenessRemover,
        AssetInterface $asset
    ) {
        $objectManager->persist($asset)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessRemover->removeForAsset($asset)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($asset);
    }

    function it_persists_the_assets_and_flushes_the_unit_of_work_and_does_not_schedule_completeness(
        $objectManager,
        $eventDispatcher,
        $completenessRemover,
        AssetInterface $asset1,
        AssetInterface $asset2
    ) {
        $objectManager->persist($asset1)->shouldBeCalled();
        $objectManager->persist($asset2)->shouldBeCalled();
        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
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
                'Expects a "Akeneo\Asset\Component\Model\AssetInterface", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
