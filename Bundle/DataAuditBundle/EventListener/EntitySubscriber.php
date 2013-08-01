<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;
use Oro\Bundle\DataAuditBundle\Metadata\ExtendMetadataFactory;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class EntitySubscriber implements EventSubscriber
{
    /**
     * @var ExtendMetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var LoggableManager
     */
    protected $loggableManager;

    /**
     * @param LoggableManager       $loggableManager
     * @param ExtendMetadataFactory $metadataFactory
     * @param ConfigProvider        $auditConfigProvider
     */
    public function __construct(LoggableManager $loggableManager, ExtendMetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
        $this->loggableManager = $loggableManager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'loadClassMetadata',
            'postPersist',
        );
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->loggableManager->handleLoggable($event->getEntityManager());
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        $doctrineMetadata = $event->getClassMetadata();

        if ($doctrineMetadata->getReflectionClass()
            && $metadata = $this->metadataFactory->extendLoadMetadataForClass($event->getClassMetadata())
        ) {
            $this->loggableManager->addConfig($metadata);
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->loggableManager->handlePostPersist($event->getEntity(), $event->getEntityManager());
    }
}
