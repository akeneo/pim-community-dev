<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Webhook;

use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventDataCollection
{
    private array $events;

    public function setEventData(EventInterface $event, array $data): self
    {
        $uuid = $event->getUuid();

        if (isset($this->events[$uuid])) {
            throw new \InvalidArgumentException(sprintf('Data already set for event %s.', $uuid));
        }

        $this->events[$uuid] = $data;

        return $this;
    }

    public function setEventDataError(EventInterface $event, \Throwable $error): self
    {
        $uuid = $event->getUuid();

        if (isset($this->events[$uuid])) {
            throw new \InvalidArgumentException(sprintf('Data already set for event %s.', $uuid));
        }

        $this->events[$uuid] = $error;

        return $this;
    }

    /**
     * @return null|\Throwable|array
     */
    public function getEventData(EventInterface $event)
    {
        $uuid = $event->getUuid();

        return $this->events[$uuid] ?? null;
    }
}
