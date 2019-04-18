<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Bundle\Doctrine\Saver;

use Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Query\Channel\FindChannelCategoryCodeInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
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
        EventDispatcherInterface $eventDispatcher,
        FindChannelCategoryCodeInterface $findChannelCategoryCode
    ): void {
        $this->beConstructedWith($objectManager, $eventDispatcher, $findChannelCategoryCode);
    }

    public function it_is_a_saver(): void
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    public function it_is_a_bulk_saver(): void
    {
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    public function it_saves_a_channel(
        $objectManager,
        $eventDispatcher,
        $findChannelCategoryCode,
        ChannelInterface $channel,
        CategoryInterface $category
    ): void {
        $channel->getId()->willReturn(null);
        $channel->getCode()->willReturn('channel-code');
        $channel->getCategory()->willReturn($category);

        $category->getCode()->willReturn('category-code');

        $findChannelCategoryCode->__invoke(
            Argument::exact('channel-code')
        )->shouldBeCalled()->willReturn('category-code');

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === true;
                }
            )
        )->shouldBeCalled();

        $objectManager->persist($channel)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === true;
                }
            )
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(ChannelCategoryHasBeenUpdated::EVENT_NAME),
            Argument::type(ChannelCategoryHasBeenUpdated::class)
        )->shouldNotBeCalled();

        $this->save($channel);
    }

    public function it_saves_multiple_channels(
        $objectManager,
        $eventDispatcher,
        $findChannelCategoryCode,
        ChannelInterface $channel1,
        ChannelInterface $channel2,
        CategoryInterface $category
    ): void {
        $channel1->getId()->willReturn(null);
        $channel1->getCode()->willReturn('channel-1-code');
        $channel1->getCategory()->willReturn($category);

        $channel2->getId()->willReturn(null);
        $channel2->getCode()->willReturn('channel-2-code');
        $channel2->getCategory()->willReturn($category);

        $category->getCode()->willReturn('category-code');

        $findChannelCategoryCode->__invoke(
            Argument::exact('channel-1-code')
        )->shouldBeCalled()->willReturn('category-code');

        $findChannelCategoryCode->__invoke(
            Argument::exact('channel-2-code')
        )->shouldBeCalled()->willReturn('category-code');

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE_ALL),
            Argument::that(
                function (GenericEvent $event) {
                    return count($event->getSubject()) === 2
                        && $event->getArgument('unitary') === false;
                }
            )
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === false;
                }
            )
        )->shouldBeCalledTimes(2);

        $objectManager->persist($channel1)->shouldBeCalled();
        $objectManager->persist($channel2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('unitary') === false;
                }
            )
        )->shouldBeCalledTimes(2);

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE_ALL),
            Argument::that(
                function (GenericEvent $event) {
                    return count($event->getSubject()) === 2
                        && $event->getArgument('unitary') === false;
                }
            )
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(ChannelCategoryHasBeenUpdated::EVENT_NAME),
            Argument::type(ChannelCategoryHasBeenUpdated::class)
        )->shouldNotBeCalled();

        $this->saveAll([$channel1, $channel2]);
    }

    public function it_adds_the_option_is_new_when_a_channel_is_created(
        $eventDispatcher,
        $findChannelCategoryCode,
        ChannelInterface $channel,
        CategoryInterface $category
    ): void {
        $channel->getId()->willReturn(0);
        $channel->getCode()->willReturn('channel-code');
        $channel->getCategory()->willReturn($category);

        $category->getCode()->willReturn('category-code');

        $findChannelCategoryCode->__invoke(
            Argument::exact('channel-code')
        )->shouldBeCalled()->willReturn(null);

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === false;
                }
            )
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === false;
                }
            )
        )->shouldBeCalled();

        $this->save($channel);
    }

    public function it_doesnt_add_the_option_is_new_when_a_channel_is_updated(
        $eventDispatcher,
        $findChannelCategoryCode,
        ChannelInterface $channel,
        CategoryInterface $category
    ): void {
        $channel->getId()->willReturn(null);
        $channel->getCode()->willReturn('channel-code');
        $channel->getCategory()->willReturn($category);

        $category->getCode()->willReturn('category-code');

        $findChannelCategoryCode->__invoke(
            Argument::exact('channel-code')
        )->shouldBeCalled()->willReturn('category-code');

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === true;
                }
            )
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE),
            Argument::that(
                function (GenericEvent $event) {
                    return $event->getSubject() instanceof ChannelInterface
                        && $event->getArgument('is_new') === true;
                }
            )
        )->shouldBeCalled();

        $this->save($channel);
    }

    public function it_triggers_a_specific_event_when_a_channel_category_is_updated(
        $eventDispatcher,
        $findChannelCategoryCode,
        ChannelInterface $channel,
        CategoryInterface $category
    ): void {
        $channel->getId()->willReturn(null);
        $channel->getCode()->willReturn('channel-code');
        $channel->getCategory()->willReturn($category);

        $category->getCode()->willReturn('new-category-code');

        $findChannelCategoryCode->__invoke(
            Argument::exact('channel-code')
        )->shouldBeCalled()->willReturn('old-category-code');

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::PRE_SAVE),
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(StorageEvents::POST_SAVE),
            Argument::type(GenericEvent::class)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::exact(ChannelCategoryHasBeenUpdated::EVENT_NAME),
            Argument::that(
                function (ChannelCategoryHasBeenUpdated $event) {
                    return $event->getChannelCode() === 'channel-code'
                        && $event->getCategoryCode() === 'new-category-code';
                }
            )
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
