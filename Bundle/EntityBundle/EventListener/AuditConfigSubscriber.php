<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Metadata\Cache\FileCache;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Event\OnFlushConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

class AuditConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigProvider
     */
    protected $auditConfigProvider;

    /**
     * @var FileCache
     */
    protected $auditMetadataFileCache;

    /**
     * @param ConfigProvider $auditConfigProvider
     * @param FileCache      $auditMetadataFileCache
     */
    public function __construct(ConfigProvider $auditConfigProvider, FileCache $auditMetadataFileCache)
    {
        $this->auditConfigProvider    = $auditConfigProvider;
        $this->auditMetadataFileCache = $auditMetadataFileCache;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::ON_FLUSH => 'onFlush',
        );
    }

    /**
     * @param OnFlushConfigEvent $event
     */
    public function onFlush(OnFlushConfigEvent $event)
    {
        $clearClassNames = array();

        $configManager = $event->getConfigManager();

        foreach ($configManager->getUpdatedEntityConfig('audit') as $entityConfig) {
            $changes = $configManager->getConfigChangeSet($entityConfig);
            if (in_array('auditable', $changes)) {
                $clearClassNames[] = $entityConfig->getClassName();

                continue;
            }
        }

        foreach ($configManager->getUpdatedFieldConfig(null, 'audit') as $fieldConfig) {
            if (in_array($fieldConfig->getClassName(), $clearClassNames)) {
                continue;
            }

            $changes = $configManager->getConfigChangeSet($fieldConfig);
            if (in_array('auditable', $changes)) {
                $clearClassNames[] = $fieldConfig->getClassName();

                continue;
            }
        }

        foreach ($clearClassNames as $className) {
            $this->auditMetadataFileCache->evictClassMetadataFromCache(new \ReflectionClass($className));
        }
    }
}
