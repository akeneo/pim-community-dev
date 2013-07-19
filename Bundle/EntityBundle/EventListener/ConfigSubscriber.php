<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Oro\Bundle\EntityConfigBundle\Event\OnFlushConfigEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\NewFieldEvent;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityEvent;
use Oro\Bundle\EntityConfigBundle\Event\Events;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::NEW_ENTITY => 'newEntity',
            Events::NEW_FIELD  => 'newField',
            Events::ON_FLUSH   => 'onFlush',
        );
    }

    /**
     * @param NewEntityEvent $event
     */
    public function newEntity(NewEntityEvent $event)
    {
        $entityProvider   = $event->getConfigManager()->getProvider('entity');
        $datagridProvider = $event->getConfigManager()->getProvider('datagrid');
        $datagridProvider = $event->getConfigManager()->getProvider('audit');
    }

    /**
     * @param NewFieldEvent $event
     */
    public function newField(NewFieldEvent $event)
    {

    }

    public function onFlush(OnFlushConfigEvent $event)
    {
        //$event->getConfigManager()->getConfigChangeSet()
        $auditProvider = $event->getConfigManager()->getProvider('audit');
    }
}
