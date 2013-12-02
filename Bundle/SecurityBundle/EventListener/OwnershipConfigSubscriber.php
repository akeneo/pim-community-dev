<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;

class OwnershipConfigSubscriber implements EventSubscriberInterface
{
    /** @var OwnershipMetadataProvider */
    protected $provider;

    /**
     * Constructor
     *
     * @param OwnershipMetadataProvider $provider
     */
    public function __construct(OwnershipMetadataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_PERSIST_CONFIG => 'prePersistEntityConfig'
        );
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function prePersistEntityConfig(PersistConfigEvent $event)
    {
        $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
        $changes = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        $isDeleted = false;
        // Now if you press delete entity button, in state variable, in 1st position will be "Deleted" string.
        // If you press restore entity, then "Deleted" string will be at 0 position.
        if (isset($changes['state']) && $changes['state'][1] === 'Deleted') {
            $isDeleted = true;
        }

        $cp = $event->getConfigManager()->getProvider('ownership');
        $className = $event->getConfig()->getId()->getClassName();
        if ($cp->hasConfig($className)) {
            $this->provider->clearCache($className);
            if (!$isDeleted) {
                $this->provider->warmUpCache($className);
            }
        }
    }
}
