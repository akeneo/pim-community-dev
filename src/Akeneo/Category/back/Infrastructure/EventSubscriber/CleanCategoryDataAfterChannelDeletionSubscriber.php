<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CleanCategoryDataAfterChannelDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FeatureFlag $enrichedCategoryFeature,
        private Connection $dbalConnection
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

        if (!$channel instanceof ChannelInterface || !$this->enrichedCategoryFeature->isEnabled()) {
            return;
        }


    }
}
