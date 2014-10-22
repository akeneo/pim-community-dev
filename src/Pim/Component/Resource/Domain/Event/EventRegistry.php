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
    const BULK_PREFIX = 'bulk_';

    /** @var ResourceEventInterface[] */
    protected $events = [];

    /**
     * @return ResourceEventInterface[] array which key is type of the event of the resource and value is the event.
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param ResourceEventInterface|ResourceBulkEventInterface $event
     *
     * @return EventRegistry
     */
    public function addEvent($event)
    {
        if ($event instanceof ResourceEventInterface) {
            $key  = get_class($event->getResource());
        } elseif($event instanceof ResourceBulkEventInterface) {
            $key  = self::BULK_PREFIX . $event->getResources()->getType();
        } else {
            throw new \InvalidArgumentException(
                'Event should be an instance of "ResourceEventInterface" or "ResourceBulkEventInterface".'
            );
        }

        $this->events[$key] = $event;

        return $this;
    }
}
