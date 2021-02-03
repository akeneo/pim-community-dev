<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\EventDispatcher;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventDispatcherObserver extends TraceableEventDispatcher
{
    private $storageEvents = [];

    protected function beforeDispatch(string $eventName, $event)
    {
        switch ($eventName) {
            case StorageEvents::POST_SAVE:
            case StorageEvents::POST_REMOVE:
                $this->incrementStorageEvents($eventName, $event);
                break;
        }

        parent::beforeDispatch($eventName, $event);
    }

    private function incrementStorageEvents(string $eventName, GenericEvent $event): void
    {
        $className = get_class($event->getSubject());

        if (!isset($this->storageEvents[$eventName][$className])) {
            $this->storageEvents[$eventName][$className] = 0;
        }

        $this->storageEvents[$eventName][$className] += 1;
    }

    public function getStorageEventCount(string $eventName, string $className): int
    {
        return $this->storageEvents[$eventName][$className] ?? 0;
    }
}
