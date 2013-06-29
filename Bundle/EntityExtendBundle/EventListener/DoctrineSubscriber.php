<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Zend\Code\Reflection\ClassReflection;

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

    public function preRemove(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof ExtendProxyInterface) {
            $this->exm->remove($event->getEntity());
        }
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof ExtendProxyInterface) {
            $this->exm->persist($event->getEntity());
        }
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        //if ($this->exm->isExtend($event->getEntity())) {
        //    $this->exm->persist($event->getEntity());
        //}
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof ExtendProxyInterface) {
            $this->exm->load($event->getEntity());
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        if ($this->exm->isExtend($event->getClassMetadata()->name)) {
            $proxyRef = new ClassReflection($this->exm->getProxyClass($event->getClassMetadata()->name));

            $event->getClassMetadata()->name      = $proxyRef->getName();
            $event->getClassMetadata()->namespace = $proxyRef->getNamespaceName();
        }
    }
}
