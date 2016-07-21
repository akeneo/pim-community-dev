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
            StorageEvents::POST_SAVE => 'synchroEventStore',
        ];
    }

    public function synchroEventStore(GenericEvent $event)
    {
        $object = $event->getSubject();
        if ($object instanceof ProductInterface) {
            $this->logger->info(sprintf('Product %s has been saved ', $object->getIdentifier()));
        }
    }
}
