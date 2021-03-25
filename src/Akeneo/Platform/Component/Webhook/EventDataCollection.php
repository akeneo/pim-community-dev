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
    private array $versions;

    public function setEventData(EventInterface $event, array $data, ?string $version = null): self
    {
        $uuid = $event->getUuid();

        if (isset($this->events[$uuid])) {
            throw new \InvalidArgumentException(sprintf('Data already set for event %s.', $uuid));
        }

        $this->events[$uuid] = $data;
        $this->versions[$uuid] = $version;

        return $this;
    }

    public function setEventDataError(EventInterface $event, \Throwable $error): self
    {
        $uuid = $event->getUuid();

        if (isset($this->events[$uuid])) {
            throw new \InvalidArgumentException(sprintf('Data already set for event %s.', $uuid));
        }

        $this->events[$uuid] = $error;
        $this->versions[$uuid] = null;

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

    public function getEventVersion(EventInterface $event): ?string
    {
        $uuid = $event->getUuid();

        return $this->versions[$uuid] ?? null;
    }
}
