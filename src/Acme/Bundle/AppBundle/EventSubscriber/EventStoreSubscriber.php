<?php

namespace Acme\Bundle\AppBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EventStoreSubscriber implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'preSave',
            StorageEvents::POST_SAVE => 'postSave',
        ];
    }

    public function preSave(GenericEvent $event)
    {
        $object = $event->getSubject();
        if ($object instanceof ProductInterface) {
            $this->logger->info(
                sprintf('Product %s will be saved, product name is now %s ', $object->getIdentifier(), $object->getValue('name'))
            );
        }
    }

    public function postSave(GenericEvent $event)
    {
        $object = $event->getSubject();
        if ($object instanceof ProductInterface) {
            $this->logger->info(
                sprintf('Product %s has been saved, product name is now %s ', $object->getIdentifier(), $object->getValue('name'))
            );
        }
    }
}
