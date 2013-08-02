<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Metadata\MetadataFactory;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewFieldConfigModelEvent;
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
            Events::NEW_ENTITY_CONFIG_MODEL   => 'newConfigModel',
            Events::PRE_PERSIST_CONFIG => 'persistConfig',
        );
    }

    /**
     * @param NewConfigModelEvent $event
     */
    public function newConfigModel(NewFieldConfigModelEvent $event)
    {
        /** @var ExtendClassMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($event->getConfigModel());
        if ($metadata && $metadata->isExtend) {
            $extendClass = $this->extendManager->getClassGenerator()->generateExtendClassName($event->getConfigModel());
            $proxyClass  = $this->extendManager->getClassGenerator()->generateProxyClassName($event->getConfigModel());

            $this->extendManager->getConfigProvider()->createConfig(
                $event->getConfigModel(),
                $values = array(
                    'is_extend'    => true,
                    'extend_class' => $extendClass,
                    'proxy_class'  => $proxyClass,
                    'owner'        => 'System',
                )
            );
        }
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function persistConfig(PersistConfigEvent $event)
    {
        $event->getConfigManager()->calculateConfigChangeSet($event->getConfig());
        $change = $event->getConfigManager()->getConfigChangeSet($event->getConfig());

        if ($event->getConfig()->getScope() == 'extend'
            && $event->getConfig()->is('is_extend')
            && count(array_intersect_key(array_flip(array('length', 'precision', 'scale')), $change))
            && $event->getConfig()->get('state') != ExtendManager::STATE_NEW
        ) {
            $entityConfig = $event->getConfigManager()->getProvider($event->getConfig()->getScope())->getConfig($event->getConfig()->getClassName());
            $event->getConfig()->set('state', ExtendManager::STATE_UPDATED);
            $entityConfig->set('state', ExtendManager::STATE_UPDATED);

            $event->getConfigManager()->persist($entityConfig);
        }
    }
}
