<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ApiAggregatorForAttributePostSaveEventSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    private bool $isActivated;

    private array $eventsAttributes;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->isActivated = false;
        $this->eventsAttributes = [];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priority must be high in order to catch events before any other subscribers.
            StorageEvents::POST_SAVE => ['aggregateEvent', 10000],
        ];
    }

    public function activate(): void
    {
        $this->isActivated = true;
    }

    public function deactivate(): void
    {
        $this->isActivated = false;
    }

    public function aggregateEvent(GenericEvent $event)
    {
        $attribute = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (!$this->isActivated || !$attribute instanceof AttributeInterface || !$unitary) {
            return;
        }

        $this->eventsAttributes[$attribute->getId()] = $attribute;

        $event->setArgument('unitary', false);
    }

    public function dispatchAllEvents(): void
    {
        if (empty($this->eventsAttributes)) {
            return;
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($this->eventsAttributes));
        $this->eventsAttributes = [];
    }
}
