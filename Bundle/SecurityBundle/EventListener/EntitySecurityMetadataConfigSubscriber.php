<?php

namespace Oro\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\PersistConfigEvent;

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
            Events::PRE_PERSIST_CONFIG => 'prePersistEntityConfig'
        );
    }

    /**
     * @param PersistConfigEvent $event
     */
    public function prePersistEntityConfig(PersistConfigEvent $event)
    {
        $cp = $event->getConfigManager()->getProvider('security');
        $className = $event->getConfig()->getId()->getClassName();
        if ($cp->hasConfig($className)) {
            $config = $cp->getConfig($className);
            $this->provider->clearCache($config->get('type'));
        }
    }
}
