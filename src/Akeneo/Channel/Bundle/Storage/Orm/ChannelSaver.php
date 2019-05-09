<?php
declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Storage\Orm;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Saver\ChannelSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelSaver implements ChannelSaverInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ChannelInterface $channel
     */
    public function save($channel, array $options = [])
    {
        $options['unitary'] = true;

        $this->doSaveAll([$channel], $options);
    }

    /**
     * @param ChannelInterface[] $channels
     */
    public function saveAll(array $channels, array $commonOptions = [])
    {
        $commonOptions['unitary'] = false;

        $this->doSaveAll($channels, $commonOptions);
    }

    /**
     * @param ChannelInterface[] $channels
     */
    private function doSaveAll(array $channels, array $commonOptions = []): void
    {
        if (empty($channels)) {
            return;
        }

        $data = $this->formatDataOptionsAndEvents($channels, $commonOptions);

        $this->dispatchPreSaveEventsAndPersist($data, $data, $commonOptions);
        $this->objectManager->flush();

        $this->dispatchPostSaveEvents($data, $channels, $commonOptions);
        $this->dispatchChannelEvents($data);
    }

    private function formatDataOptionsAndEvents(array $channels, array $commonOptions) : array
    {
        return array_map(
            function (ChannelInterface $channel) use ($commonOptions) {
                return [
                    $channel,
                    array_merge($commonOptions, ['is_new' => null === $channel->getId()]),
                    $channel->popEvents()
                ];
            },
            $channels
        );
    }

    private function dispatchPreSaveEventsAndPersist(array $data, array $channels, array $commonOptions): void
    {
        if (false === $commonOptions['unitary']) {
            $this->eventDispatcher->dispatch(
                StorageEvents::PRE_SAVE_ALL,
                new GenericEvent($channels, $commonOptions)
            );
        }

        foreach ($data as [$channel, $options]) {
            $this->eventDispatcher->dispatch(
                StorageEvents::PRE_SAVE,
                new GenericEvent($channel, $options)
            );

            $this->objectManager->persist($channel);
        }
    }

    private function dispatchPostSaveEvents(array $data, array $channels, array $commonOptions): void
    {
        foreach ($data as [$channel, $options]) {
            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE,
                new GenericEvent($channel, $options)
            );
        }

        if (false === $commonOptions['unitary']) {
            $this->eventDispatcher->dispatch(
                StorageEvents::POST_SAVE_ALL,
                new GenericEvent($channels, $commonOptions)
            );
        }
    }

    private function dispatchChannelEvents(array $data): void
    {
        $channelsEvents = [];

        foreach ($data as [, , $channelEvents]) {
            $channelsEvents[] = $channelEvents;
        }

        $channelsEvents = array_merge(...$channelsEvents);

        foreach ($channelsEvents as $channelEvent) {
            $this->eventDispatcher->dispatch(get_class($channelEvent), $channelEvent);
        }
    }
}
