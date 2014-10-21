<?php

namespace Pim\Component\Resource\Domain\Event;

/**
 * Event registry.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventRegistry
{
    /** @var ResourceEvent[] */
    protected $events = [];

    /**
     * @return ResourceEvent[] array which key is the class of the resource and value is the event.
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param ResourceEvent[] $events
     *
     * @return EventRegistry
     */
    public function setEvents(array $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * @param ResourceEvent $event
     *
     * @return EventRegistry
     */
    public function addEvent(ResourceEvent $event)
    {
        $this->events[get_class($event->getResource())] = $event;

        return $this;
    }
}
