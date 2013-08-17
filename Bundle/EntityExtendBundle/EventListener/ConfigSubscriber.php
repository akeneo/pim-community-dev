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
     * @param ExtendManager $extendManager
     */
    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
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
        $config = $this->extendManager->getConfigProvider()->getConfig($event->getClassName());
        if ($config->get('is_extend')) {
            $extendClass = $this->extendManager->getClassGenerator()->generateExtendClassName(
                $event->getClassName()
            );
            $proxyClass  = $this->extendManager->getClassGenerator()->generateProxyClassName(
                $event->getClassName()
            );

            $config->set('extend_class', $extendClass);
            $config->set('proxy_class', $proxyClass);

            $this->extendManager->getConfigProvider()->persist($config);
        }
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        $scope     = $event->getConfig()->getId()->getScope();
        $className = $event->getConfig()->getId()->getClassName();

        if ($scope == 'extend'
            && $event->getConfig()->is('is_extend')
            && count(array_intersect_key(array_flip(array('length', 'precision', 'scale')), $change))
        ) {
            $entityConfig = $event->getConfigManager()
                ->getProvider($scope)
                ->getConfig($className);

            if ($event->getConfig()->get('state') != ExtendManager::STATE_NEW) {
                $event->getConfig()->set('state', ExtendManager::STATE_UPDATED);

                $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
            }

            if ($entityConfig->get('state') != ExtendManager::STATE_NEW) {
                $entityConfig->set('state', ExtendManager::STATE_UPDATED);

                $event->getConfigManager()->persist($entityConfig);
            }
        }
    }
}
