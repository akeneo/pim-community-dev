<?php

namespace Specification\Akeneo\Asset\Bundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetVariationSaverSpec extends ObjectBehavior
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
        $this->shouldHaveType(SaverInterface::class);
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_persists_the_variation_and_flushes_the_unit_of_work_and_schedule_completeness_for_the_asset(
        $objectManager,
        $eventDispatcher,
        $completenessRemover,
        VariationInterface $variation,
        AssetInterface $asset
    ) {
        $objectManager->persist($variation)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $variation->getAsset()->willReturn($asset);
        $completenessRemover->removeForAsset($asset)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($variation);
    }

    function it_persists_the_variations_and_flushes_the_unit_of_work_and_does_not_schedule_completeness(
        $objectManager,
        $eventDispatcher,
        $completenessRemover,
        VariationInterface $variation1,
        VariationInterface $variation2
    ) {
        $objectManager->persist($variation1)->shouldBeCalled();
        $objectManager->persist($variation2)->shouldBeCalled();
        $completenessRemover->removeForAsset(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->saveAll([$variation1, $variation2]);
    }

    function it_throws_exception_when_save_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                VariationInterface::class,
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
