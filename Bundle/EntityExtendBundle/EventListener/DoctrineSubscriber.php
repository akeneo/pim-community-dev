<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;
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
            'postLoad',
            'loadClassMetadata'
        );
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        /*if ($event->getEntity() instanceof ExtendProxyInterface) {
            $event->getEntityManager()->remove($event->getEntity()->__proxy__getExtend());
        }*/
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof ExtendProxyInterface) {
            $event->getEntityManager()->remove($event->getEntity()->__proxy__getExtend());
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        /*if ($event->getEntity() instanceof ExtendProxyInterface
            || $this->exm->isExtend($event->getEntity())
        ) {
            $this->exm->persist($event->getEntity());
        }*/
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        /*if ($event->getEntity() instanceof ExtendProxyInterface
            || $this->exm->isExtend($event->getEntity())
        ) {
            $this->exm->loadExtend($event->getEntity());
        }*/
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /*if ($this->exm->isExtend($event->getClassMetadata()->name)) {
            $proxyRef = new \ReflectionClass($this->exm->getProxyClass($event->getClassMetadata()->name));

            $event->getClassMetadata()->name      = $proxyRef->getName();
            $event->getClassMetadata()->namespace = $proxyRef->getNamespaceName();
        }*/
    }
}
