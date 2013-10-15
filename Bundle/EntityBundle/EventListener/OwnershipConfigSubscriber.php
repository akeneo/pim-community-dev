<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
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
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        $isDeleted = false;
        if (isset($change['state']) && $change['state'][1] === 'Deleted') {
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
