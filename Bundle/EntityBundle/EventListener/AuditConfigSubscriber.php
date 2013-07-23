<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Metadata\Cache\FileCache;
use Metadata\MetadataFactory;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityBundle\Metadata\AuditEntityMetadata;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Event\FlushConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

class AuditConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConfigProvider
     */
    protected $auditConfigProvider;

    /**
     * @var MetadataFactory
     */
    protected $auditMetadataFactory;

    /**
     * @var FileCache
     */
    protected $auditMetadataFileCache;

    /**
     * @param ConfigProvider  $auditConfigProvider
     * @param MetadataFactory $auditMetadataFactory
     * @param FileCache       $auditMetadataFileCache
     */
    public function __construct(ConfigProvider $auditConfigProvider, MetadataFactory $auditMetadataFactory, FileCache $auditMetadataFileCache)
    {
        $this->auditConfigProvider    = $auditConfigProvider;
        $this->auditMetadataFileCache = $auditMetadataFileCache;
        $this->auditMetadataFactory   = $auditMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::ON_FLUSH   => 'onFlush',
            Events::NEW_ENTITY => 'newEntity',
        );
    }

    /**
     * @param FlushConfigEvent $event
     */
    public function onFlush(FlushConfigEvent $event)
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

    public function newEntity(NewEntityEvent $event)
    {
        /** @var AuditEntityMetadata $metadata */
        $metadata = $this->auditMetadataFactory->getMetadataForClass($event->getClassName());
        if ($metadata && $metadata->auditable) {
            var_dump($metadata);
        }
        die('hi');
    }
}
