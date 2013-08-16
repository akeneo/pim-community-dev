<?php

namespace Oro\Bundle\EmailBundle\EventListener;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;

class ConfigSubscriber implements EventSubscriberInterface
{
    /** @var \Doctrine\Common\Cache\Cache */
    protected $cache;

    /** @var  string */
    protected $cacheKey;

    public function __construct(Cache $cache, $cacheKey)
    {
        $this->cache    = $cache;
        $this->cacheKey = $cacheKey;
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
        // clear cache when new entity added to configurator
        // in case if default value for some fields will equal true
        $cp = $event->getConfigManager()->getProvider('email');
        $fieldConfigs = $cp->filter(
            function (ConfigInterface $config) {
                return $config->is('available_in_template');
            },
            $event->getClassName()
        );

        if (count($fieldConfigs)) {
            $this->cache->delete($this->cacheKey);
        }
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        if ($event->getConfig()->getConfigId()->getScope() == 'email' && isset($change['available_in_template'])) {
            $this->cache->delete($this->cacheKey);
        }
    }
}
