<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @var ExtendManager
     */
    protected $exm;

    /**
     * @param ExtendManager $extendManager
     */
    public function __construct(ExtendManager $extendManager)
    {
        $this->exm = $extendManager;
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
            'loadClassMetadata',
            'postLoad'
        );
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        if ($this->exm->isExtend($event->getEntity())) {
//            $this->exm->remove($event->getEntity());
        }
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        if ($this->exm->isExtend($event->getEntity())) {
//            $this->exm->persist($event->getEntity());
        }
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        if ($this->exm->isExtend($event->getEntity())) {
//            $this->exm->persist($event->getEntity());
        }
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        if ($this->exm->isExtend($event->getEntity())) {
//            $this->exm->load($event->getEntity());
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
    }
}
