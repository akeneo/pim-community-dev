<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Metadata\MetadataFactory;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

use Oro\Bundle\EntityExtendBundle\Metadata\ExtendClassMetadata;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    protected $postFlushConfig = array();

    /**
     * @param ExtendManager   $extendManager
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(ExtendManager $extendManager, MetadataFactory $metadataFactory)
    {
        $this->extendManager   = $extendManager;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::NEW_ENTITY_CONFIG_MODEL => 'newConfigModel',
            Events::PRE_PERSIST_CONFIG      => 'persistConfig',
        );
    }

    /**
     * @param NewEntityConfigModelEvent $event
     */
    public function newConfigModel(NewEntityConfigModelEvent $event)
    {
        if (class_exists($event->getClassName())) {
            $metadata = $this->metadataFactory->getMetadataForClass($event->getClassName());
            if ($metadata && $metadata->isExtend) {
                $extendClass = $this->extendManager->getClassGenerator()->generateExtendClassName($event->getClassName());
                $proxyClass  = $this->extendManager->getClassGenerator()->generateProxyClassName($event->getClassName());

                $config = $this->extendManager->getConfigProvider()->getConfig($event->getClassName());
                $config->set('is_extend', true);
                $config->set('extend_class', $extendClass);
                $config->set('proxy_class', $proxyClass);

                $this->extendManager->getConfigProvider()->persist($config);
            }
        }
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        //$event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        $scope     = $event->getConfig()->getConfigId()->getScope();
        $className = $event->getConfig()->getConfigId()->getClassName();

        if ($scope == 'extend'
            && $event->getConfig()->is('is_extend')
            && count(array_intersect_key(array_flip(array('length', 'precision', 'scale')), $change))
            && $event->getConfig()->get('state') != ExtendManager::STATE_NEW
        ) {
            $entityConfig = $event->getConfigManager()
                ->getProvider($scope)
                ->getConfig($className);

            $event->getConfig()->set('state', ExtendManager::STATE_UPDATED);
            $entityConfig->set('state', ExtendManager::STATE_UPDATED);

            $event->getConfigManager()->persist($entityConfig);
        }
    }
}
