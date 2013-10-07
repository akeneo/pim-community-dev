<?php

namespace Oro\Bundle\EntityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\EntityConfigBundle\Event\Events;
use Oro\Bundle\EntityConfigBundle\Event\NewEntityConfigModelEvent;

class OwnershipConfigSubscriber implements EventSubscriberInterface
{
    /** @var OwnershipMetadataProvider */
    protected $provider;

    /**
     * Constructor
     *
     * @param OwnershipMetadataProvider $provider
     */
    public function __construct(OwnershipMetadataProvider $provider)
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
        $cp = $event->getConfigManager()->getProvider('ownership');
        if ($cp->hasConfig($event->getClassName())) {
            $this->provider->warmUpCache($event->getClassName());
        }
    }
}
