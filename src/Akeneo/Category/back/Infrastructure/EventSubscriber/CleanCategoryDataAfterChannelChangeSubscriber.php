<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Enrichment\CleanCategoryDataLinkedToChannel;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CleanCategoryDataAfterChannelChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CleanCategoryDataLinkedToChannel $cleanCategoryDataLinkedToChannel,
        private readonly FeatureFlag $enrichedCategoryFeature,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'cleanCategoryDataForChannelLocale',
            StorageEvents::POST_REMOVE => 'cleanCategoryDataForChannel',
        ];
    }

    public function cleanCategoryDataForChannel(GenericEvent $event): void
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface || !$this->enrichedCategoryFeature->isEnabled()) {
            return;
        }

        ($this->cleanCategoryDataLinkedToChannel)($channel, CleanCategoryDataLinkedToChannel::CLEAN_CHANNEL_ACTION);
    }

    public function cleanCategoryDataForChannelLocale(GenericEvent $event): void
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface || !$this->enrichedCategoryFeature->isEnabled()) {
            return;
        }

        ($this->cleanCategoryDataLinkedToChannel)($channel, CleanCategoryDataLinkedToChannel::CLEAN_CHANNEL_LOCALE_ACTION);
    }
}
