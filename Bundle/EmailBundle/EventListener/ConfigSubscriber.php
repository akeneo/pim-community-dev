<?php

namespace Oro\Bundle\EmailBundle\EventListener;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityEvent;

class ConfigSubscriber implements EventSubscriberInterface
{
    /** @var \Doctrine\Common\Cache\Cache */
    protected $cache;

    /** @var  string */
    protected $cacheKey;

    public function __construct(Cache $cache, $cacheKey)
    {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::NEW_ENTITY     => 'newEntityConfig',
            Events::PERSIST_CONFIG => 'persistConfig',
        );
    }

    /**
     * @param NewEntityEvent $event
     */
    public function newEntityConfig(NewEntityEvent $event)
    {
        // clear cache when new entity added to configurator
        // in case if default value for some fields will equal true
        $cm = $event->getConfigManager();
        if ($cm->hasConfig($event->getClassName())) {
            $config = $cm->getConfig($event->getClassName(), 'email');
            $fields = $config->getFields(
                function (FieldConfig $field) {
                    return $field->is('available_in_template');
                }
            );

            if (!$fields->isEmpty()) {
                $this->cache->delete($this->cacheKey);
            }
        }
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        if ($event->getConfig()->getScope() == 'email' && isset($change['available_in_template'])) {
            $this->cache->delete($this->cacheKey);
        }
    }
}
