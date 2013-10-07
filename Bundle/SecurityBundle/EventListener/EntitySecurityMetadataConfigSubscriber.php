<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;

class EntitySecurityMetadataConfigSubscriber implements EventSubscriberInterface
{
    /** @var EntitySecurityMetadataProvider */
    protected $provider;

    /**
     * Constructor
     *
     * @param EntitySecurityMetadataProvider $provider
     */
    public function __construct(EntitySecurityMetadataProvider $provider)
    {
        $this->provider = $provider;
    }

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
        if ($cp->hasConfig($event->getClassName())) {
            $config = $cp->getConfig($event->getClassName());
            $this->provider->clearCache($config->get('type'));
        }
    }
}
