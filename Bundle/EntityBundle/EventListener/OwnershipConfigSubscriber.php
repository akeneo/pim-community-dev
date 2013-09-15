<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
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
            Events::NEW_ENTITY_CONFIG_MODEL => 'newEntityConfig',
            Events::PRE_PERSIST_CONFIG      => 'persistConfig',
        );
    }

    /**
     * @param NewEntityConfigModelEvent $event
     */
    public function newEntityConfig(NewEntityConfigModelEvent $event)
    {
        var_dump('newEntityConfig');
        // clear cache when new entity added to configurator
        // in case if default value for some fields will equal true
//        $cp = $event->getConfigManager()->getProvider('email');
//        $fieldConfigs = $cp->filter(
//            function (ConfigInterface $config) {
//                return $config->is('available_in_template');
//            },
//            $event->getClassName()
//        );
//
//        if (count($fieldConfigs)) {
//            $this->cache->delete($this->cacheKey);
//        }
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        var_dump('persistConfig');

//        $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
//        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());
//
//        if ($event->getConfig()->getId()->getScope() == 'ownership' && isset($change['available_in_template'])) {
//            $this->provider->clearCache($this->cacheKey);
//        }
    }
}
