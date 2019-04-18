<?php

declare(strict_types=1);

namespace Akeneo\Channel\Bundle\Doctrine\Saver;

use Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Query\Channel\FindChannelCategoryCodeInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var FindChannelCategoryCodeInterface */
    private $findChannelCategoryCode;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        FindChannelCategoryCodeInterface $findChannelCategoryCode
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->findChannelCategoryCode = $findChannelCategoryCode;
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

        $data = \array_map(
            function (ChannelInterface $channel) use ($commonOptions) {
                return [
                    $channel,
                    \array_merge($commonOptions, ['is_new' => null === $channel->getId()]),
                    $this->isChannelCategoryUpdated($channel->getCode(), $channel->getCategory()->getCode()),
                ];
            },
            $channels
        );

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

        $this->objectManager->flush();

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

        foreach ($data as [$channel, , $channelCategoryUpdated]) {
            if (true === $channelCategoryUpdated) {
                $this->eventDispatcher->dispatch(
                    ChannelCategoryHasBeenUpdated::EVENT_NAME,
                    new ChannelCategoryHasBeenUpdated(
                        $channel->getCode(),
                        $channel->getCategory()->getCode()
                    )
                );
            }
        }
    }

    private function isChannelCategoryUpdated(string $channelCode, string $newCategoryCode): bool
    {
        $currentCategoryCode = ($this->findChannelCategoryCode)($channelCode);
        if (null === $currentCategoryCode) {
            return false;
        }

        return $currentCategoryCode !== $newCategoryCode;
    }
}
