<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactory;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\EntityConfigEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

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
            Events::CREATE_ENTITY_CONFIG => 'newEntityConfig'
        );
    }

    /**
     * @param EntityConfigEvent $event
     */
    public function newEntityConfig(EntityConfigEvent $event)
    {
        /** @var ClassHierarchyMetadata $metadata */
        $metadata = $this->metadataFactory->getMetadataForClass($event->getClassName());
        if ($metadata->getOutsideClassMetadata()->isExtend) {
            $extendClass = $this->extendManager->getClassGenerator()->generateExtendClassName($event->getClassName());
            $proxyClass  = $this->extendManager->getClassGenerator()->generateProxyClassName($event->getClassName());

            $this->extendManager->getConfigProvider()->createEntityConfig(
                $event->getClassName(),
                $values = array(
                    'is_extend'    => true,
                    'extend_class' => $extendClass,
                    'proxy_class'  => $proxyClass,
                    'owner'        => 'System'
                )
            );
        }
    }
}
