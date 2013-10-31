<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::NEW_ENTITY_CONFIG_MODEL => 'newEntityConfig'
        );
    }

    /**
     * @param NewEntityConfigModelEvent $event
     */
    public function newEntityConfig(NewEntityConfigModelEvent $event)
    {
        $cp = $event->getConfigManager()->getProvider('security');
        $config = $cp->getConfig($event->getClassName());
        $config->set('type', EntitySecurityMetadataProvider::ACL_SECURITY_TYPE);
    }
}
