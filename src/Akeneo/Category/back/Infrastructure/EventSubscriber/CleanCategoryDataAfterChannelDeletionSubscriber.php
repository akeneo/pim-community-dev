<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Infrastructure\EventSubscriber\Cleaner\CleanCategoryDataLinkedToChannel;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CleanCategoryDataAfterChannelDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CleanCategoryDataLinkedToChannel $cleanCategoryDataLinkedToChannel,
        private readonly FeatureFlag $enrichedCategoryFeature,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'cleanCategoryData',
        ];
    }

    /**
     * @param GenericEvent $event
     * @return void
     */
    private function cleanCategoryData(GenericEvent $event): void
    {
        $channel = $event->getSubject();
        $event->setArguments();

        if (!$channel instanceof ChannelInterface || !$this->enrichedCategoryFeature->isEnabled()) {
            return;
        }

        $deletedChannelCode = $channel->getCode();
        ($this->cleanCategoryDataLinkedToChannel)($deletedChannelCode);
    }
}
