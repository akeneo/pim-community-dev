<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\CompletenessSavingOptionsResolver;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChannelSaverSpec extends ObjectBehavior
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

    function it_saves_a_channel_and_flushes_by_default($objectManager, $optionsResolver, $eventDispatcher, ChannelInterface $channel)
    {
        $channel->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'schedule' => true]);
        $objectManager->persist($channel)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $this->save($channel);
    }

    function it_saves_a_channel_and_does_not_flushe($objectManager, $optionsResolver, $eventDispatcher, ChannelInterface $channel)
    {
        $channel->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions(['flush' => false])
        ->shouldBeCalled()
        ->willReturn(['flush' => false, 'schedule' => true]);
        $objectManager->persist($channel)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $this->save($channel, ['flush' => false]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\ChannelInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
