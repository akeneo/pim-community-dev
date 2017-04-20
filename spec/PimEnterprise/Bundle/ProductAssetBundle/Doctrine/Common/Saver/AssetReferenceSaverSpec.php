<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetReferenceSaverSpec extends ObjectBehavior
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
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_persists_the_reference_and_flushes_the_unit_of_work_and_schedule_completeness_for_the_asset(
        $objectManager,
        $eventDispatcher,
        $completenessRemover,
        ReferenceInterface $reference,
        AssetInterface $asset
    ) {
        $objectManager->persist($reference)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $reference->getAsset()->willReturn($asset);
        $completenessRemover->removeForAsset($asset)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($reference);
    }

    function it_persists_the_references_and_flushes_the_unit_of_work_and_does_not_schedule_completeness(
        $objectManager,
        $eventDispatcher,
        $completenessRemover,
        ReferenceInterface $reference1,
        ReferenceInterface $reference2
    ) {
        $objectManager->persist($reference1)->shouldBeCalled();
        $objectManager->persist($reference2)->shouldBeCalled();
        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->saveAll([$reference1, $reference2]);
    }

    function it_throws_exception_when_save_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "PimEnterprise\Component\ProductAsset\Model\ReferenceInterface", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
