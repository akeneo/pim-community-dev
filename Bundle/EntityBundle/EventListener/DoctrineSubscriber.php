<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\EntityBundle\Audit\AuditManager;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @var AuditManager
     */
    protected $auditManager;

    /**
     * @param AuditManager $auditManager
     */
    public function __construct(AuditManager $auditManager)
    {
        $this->auditManager = $auditManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'preRemove',
            'preUpdate',
            'prePersist',
            'postPersist',
            'postLoad',
            'onFlush',
            'loadClassMetadata'
        );
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {

    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event)
    {

    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {

    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->auditManager->setExtendManager($event->getEntityManager());
        $this->auditManager->postLog($event->getEntity());
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {

    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->auditManager->setExtendManager($event->getEntityManager());
        //$this->auditManager->log();
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {

    }
}
