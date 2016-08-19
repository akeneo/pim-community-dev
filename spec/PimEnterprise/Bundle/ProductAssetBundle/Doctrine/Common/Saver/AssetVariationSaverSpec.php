<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetVariationSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CompletenessGeneratorInterface $completenessGenerator
    ) {
        $this->beConstructedWith($objectManager, $eventDispatcher, $completenessGenerator);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_persists_the_variation_and_flushes_the_unit_of_work_and_schedule_completeness_for_the_asset(
        $objectManager,
        $eventDispatcher,
        $completenessGenerator,
        VariationInterface $variation,
        AssetInterface $asset
    ) {
        $objectManager->persist($variation)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $variation->getAsset()->willReturn($asset);
        $completenessGenerator->scheduleForAsset($asset)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($variation);
    }

    function it_persists_the_variations_and_flushes_the_unit_of_work_and_does_not_schedule_completeness(
        $objectManager,
        $eventDispatcher,
        $completenessGenerator,
        VariationInterface $variation1,
        VariationInterface $variation2
    ) {
        $objectManager->persist($variation1)->shouldBeCalled();
        $objectManager->persist($variation2)->shouldBeCalled();
        $completenessGenerator->scheduleForAsset(Argument::any())->shouldNotBeCalled();
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
                'Expects a "PimEnterprise\Component\ProductAsset\Model\VariationInterface", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
