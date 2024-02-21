<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveLocalesFromChannelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BulkSaverInterface $localeSaver,
        private array $localesToSave = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeLocalesFromChannel',
            StorageEvents::POST_REMOVE => 'saveLocales',
        ];
    }

    public function removeLocalesFromChannel(GenericEvent $event): void
    {
        $channel = $event->getSubject();
        if (!$channel instanceof ChannelInterface) {
            return;
        }

        foreach ($channel->getLocales() as $locale) {
            $channel->removeLocale($locale);
            $this->localesToSave[$channel->getCode()][] = $locale;
        }
    }

    public function saveLocales(GenericEvent $event): void
    {
        $channel = $event->getSubject();
        if (!$channel instanceof ChannelInterface || !isset($this->localesToSave[$channel->getCode()])) {
            return;
        }

        $this->localeSaver->saveAll($this->localesToSave[$channel->getCode()]);
    }
}
