<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;
use Oro\Bundle\DataAuditBundle\Metadata\ExtendMetadataFactory;

class EntitySubscriber implements EventSubscriber
{
    protected $metadataFactory;

    protected $loggableManager;

    public function __construct(LoggableManager $loggableManager, ExtendMetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
        $this->loggableManager = $loggableManager;
    }

    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'loadClassMetadata',
            'postPersist',
        );
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        $this->loggableManager->handleLoggable($event->getEntityManager());
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        if ($metadata = $this->metadataFactory->extendLoadMetadataForClass($event->getClassMetadata())) {
            $this->loggableManager->addConfig($metadata);
        }
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->loggableManager->handlePostPersist($event->getEntity(), $event->getEntityManager());
    }
}