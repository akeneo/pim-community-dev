<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Bundle\Storage\Orm;

use Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Saver\ChannelSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChannelSaverSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith($objectManager, $eventDispatcher);
    }

    public function it_is_a_channel_saver(): void
    {
        $this->shouldHaveType(ChannelSaverInterface::class);
    }

    public function it_saves_a_channel(
        $objectManager,
        $eventDispatcher,
        ChannelInterface $channel
    ): void {
        $channel->getId()->willReturn(null);
        $channel->popEvents()->willReturn([]);

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === true;
                }
            ),
            Argument::exact(StorageEvents::PRE_SAVE)
        )->shouldBeCalled();

        $objectManager->persist($channel)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === true;
                }
            ),
            Argument::exact(StorageEvents::POST_SAVE)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::type(ChannelCategoryHasBeenUpdated::class),
            Argument::exact(ChannelCategoryHasBeenUpdated::class)
        )->shouldNotBeCalled();

        $this->save($channel);
    }

    public function it_saves_multiple_channels(
        $objectManager,
        $eventDispatcher,
        ChannelInterface $channel1,
        ChannelInterface $channel2
    ): void {
        $channel1->getId()->willReturn(null);
        $channel1->popEvents()->willReturn([]);
        $channel2->getId()->willReturn(null);
        $channel2->popEvents()->willReturn([]);

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return is_countable($event->getSubject()) &&  count($event->getSubject()) === 2
                        && $event->getArgument('unitary') === false;
                }
            ),
            Argument::exact(StorageEvents::PRE_SAVE_ALL)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === false;
                }
            ),
            Argument::exact(StorageEvents::PRE_SAVE)
        )->shouldBeCalledTimes(2);

        $objectManager->persist($channel1)->shouldBeCalled();
        $objectManager->persist($channel2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === false;
                }
            ),
            Argument::exact(StorageEvents::POST_SAVE)
        )->shouldBeCalledTimes(2);

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return is_countable($event->getSubject()) && count($event->getSubject()) === 2
                        && $event->getArgument('unitary') === false;
                }
            ),
            Argument::exact(StorageEvents::POST_SAVE_ALL)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::type(ChannelCategoryHasBeenUpdated::class),
            Argument::exact(ChannelCategoryHasBeenUpdated::class)
        )->shouldNotBeCalled();

        $this->saveAll([$channel1, $channel2]);
    }

    public function it_adds_the_option_is_new_when_a_channel_is_created(
        $eventDispatcher,
        ChannelInterface $channel
    ): void {
        $channel->getId()->willReturn(0);
        $channel->getCode()->willReturn('channel-code');
        $channel->popEvents()->willReturn([]);

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === false;
                }
            ),
            Argument::exact(StorageEvents::PRE_SAVE)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === false;
                }
            ),
            Argument::exact(StorageEvents::POST_SAVE)
        )->shouldBeCalled();

        $this->save($channel);
    }

    public function it_doesnt_add_the_option_is_new_when_a_channel_is_updated(
        $eventDispatcher,
        ChannelInterface $channel
    ): void {
        $channel->getId()->willReturn(null);
        $channel->popEvents()->willReturn([]);

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === true;
                }
            ),
            Argument::exact(StorageEvents::PRE_SAVE)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === true;
                }
            ),
            Argument::exact(StorageEvents::POST_SAVE)
        )->shouldBeCalled();

        $this->save($channel);
    }

    public function it_triggers_a_specific_event_when_a_channel_category_is_updated(
        $eventDispatcher,
        ChannelInterface $channel
    ): void {
        $channelCategoryHasBeenUpdated = new ChannelCategoryHasBeenUpdated('channel-code', 'previous-category-code', 'new-category-code');
        $channel->getId()->willReturn(null);
        $channel->popEvents()->willReturn([$channelCategoryHasBeenUpdated]);

        $eventDispatcher->dispatch(
            Argument::type(GenericEvent::class),
            Argument::exact(StorageEvents::PRE_SAVE)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::type(GenericEvent::class),
            Argument::exact(StorageEvents::POST_SAVE)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::that(
                function ($event) {
                    return $event instanceof ChannelCategoryHasBeenUpdated
                        && $event->channelCode() === 'channel-code'
                        && $event->previousCategoryCode() === 'previous-category-code'
                        && $event->newCategoryCode() === 'new-category-code';
                }
            ),
            Argument::exact(ChannelCategoryHasBeenUpdated::class)
        )->shouldBeCalled();

        $this->save($channel);
    }

    public function it_throws_an_exception_when_trying_to_save_anything_else_than_a_channel(): void
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(\TypeError::class)
            ->during('save', [$anythingElse]);
    }
}
