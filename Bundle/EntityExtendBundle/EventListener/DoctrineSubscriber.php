<?php

namespace Oro\Bundle\EntityExtendBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata'
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $event
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $event)
    {
        /** @var OroEntityManager $em */
        $em = $event->getObjectManager();

        $configProvider = $em->getExtendManager()->getConfigProvider();
        $className      = $event->getClassMetadata()->getName();

        if ($configProvider->hasConfig($className)) {
            $config = $configProvider->getConfig($className);


            //var_dump($event->getClassMetadata()->table);
        }
    }
}
