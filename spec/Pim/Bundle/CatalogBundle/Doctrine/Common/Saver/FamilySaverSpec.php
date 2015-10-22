<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\CompletenessSavingOptionsResolver;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilySaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        CompletenessSavingOptionsResolver $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $completenessManager, $optionsResolver, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_saves_a_family_and_flushes_by_default($objectManager, $optionsResolver, $eventDispatcher, FamilyInterface $family)
    {
        $family->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'schedule' => true]);
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $this->save($family);
    }

    function it_saves_a_family_and_does_not_flushe($objectManager, $optionsResolver, $eventDispatcher, FamilyInterface $family)
    {
        $family->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false, 'schedule' => true]);
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $this->save($family, ['flush' => false]);
    }

    function it_saves_a_family_and_does_not_schedule(
        $completenessManager,
        $optionsResolver,
        $objectManager,
        $eventDispatcher,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions(['schedule' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'schedule' => false]);
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->scheduleForFamily($family)->shouldNotBeCalled($family);

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $this->save($family, ['schedule' => false]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
