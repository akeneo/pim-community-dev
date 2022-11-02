<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\GenericEvent;

class DispatchProductRemovedEventSubscriber implements DispatchBufferedPimEventSubscriberInterface
{
    private DispatchBufferedPimEventSubscriberInterface $baseDispatcher;

    public function __construct(DispatchBufferedPimEventSubscriberInterface $baseDispatcher)
    {
        $this->baseDispatcher = $baseDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'createAndDispatchPimEvents',
            StorageEvents::POST_REMOVE_ALL => 'dispatchBufferedPimEvents',
        ];
    }

    public function createAndDispatchPimEvents(GenericEvent $postSaveEvent): void
    {
        if ($postSaveEvent->getSubject() instanceof PublishedProductInterface) {
            return;
        }

        $this->baseDispatcher->createAndDispatchPimEvents($postSaveEvent);
    }

    public function dispatchBufferedPimEvents(): void
    {
        $this->baseDispatcher->dispatchBufferedPimEvents();
    }
}
