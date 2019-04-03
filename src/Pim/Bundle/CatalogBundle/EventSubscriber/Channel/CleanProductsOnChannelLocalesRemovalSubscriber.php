<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber\Channel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\CatalogBundle\EventSubscriber\Event\DeactivatedLocalesOnChannelsEvent;

class CleanProductsOnChannelLocalesRemovalSubscriber implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [DeactivatedLocalesOnChannelsEvent::NAME => 'onDeactivatedLocalesOnChannel'];
    }

    public function onDeactivatedLocalesOnChannel(DeactivatedLocalesOnChannelsEvent $deactivatedLocalesOnChannelsEvent): void
    {
        //launch job : JobLauncher
    }
}
