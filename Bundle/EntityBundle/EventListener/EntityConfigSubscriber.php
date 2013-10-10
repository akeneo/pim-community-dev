<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\NewFieldConfigModelEvent;

class EntityConfigSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::NEW_FIELD_CONFIG_MODEL => 'newField'
        );
    }

    /**
     * @param NewFieldConfigModelEvent $event
     */
    public function newField(NewFieldConfigModelEvent $event)
    {
        $configProvider = $event->getConfigManager()->getProvider('entity');
        $config = $configProvider->getConfig($event->getClassName(), $event->getFieldName());
        if (!$config->is('label')) {
            $config->set('label', $event->getFieldName());
        }
    }
}
